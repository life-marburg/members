let
    unstable = import <nixos-unstable> {};
in
{ pkgs ? import <nixpkgs> {} }:
  pkgs.mkShell {
    nativeBuildInputs = with pkgs; [
      (php80.withExtensions
        ({ all, ... }: with all; [
          bz2
          curl
          dom
          filter
          fileinfo
          gd
          iconv
          imagick
          intl
          #json
          mbstring
          openssl
          pdo
          pdo_mysql
          pdo_sqlite
          session
          sodium
          sqlite3
          tokenizer
          xdebug
          xmlwriter
          yaml
          zip
          zlib
        ])
      )
      unstable.php80Packages.composer # composer 2
      libjpeg
 ];
}
