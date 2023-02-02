<x-content title="Calendar">
    <div class="prose max-w-full">
        <h2>Interner Life-Kalender</h2>
        <p>
            Du kannst den Kalender <a href="https://life-marburg.de/kalender.html">auf der Homepage</a> ansehen, um
            einen schnellen Überblick zu bekommen.
        </p>

        <p>
            Du kannst den Kalender auch auf deinem Mobilgerät einrichten, so dass du immer alle Termine automatisch
            siehst und Erinnerungen bekommst. Das geht so:
        </p>

        <h3>Unter iOS</h3>

        <ol>
            <li>Öffne in den Einstellungen auf „Kalender“ und dann „Accounts“</li>
            <li>Tippe in den Account-Einstellungen unten in der Account-Liste auf „Account hinzufügen“ und dann „Andere“</li>
            <li>Tippe im nächsten Auswahldialog auf „Kalenderabo hinzufügen“</li>
            <li>Trage unter „Server“ die URL <code>{{ route('caldav.calendar.internal') }}</code> ein</li>
            <li>
                Gib als Benutzername deine E-Mail-Adresse (<code>{{ Auth::user()->email }}</code>) und dein Passwort an
            </li>
            <li>Tippe oben rechts auf "Sichern" um das Abo zu speichern</li>
            <li>Fertig! Es kann etwas dauern, bis alle Termine aus dem Kalender sichtbar sind</li>
        </ol>

        <h3>Unter Android</h3>

        <ol>
            <li>Du brauchst zuerst die App <a href="https://play.google.com/store/apps/details?id=at.bitfire.icsdroid"
                                              target="_blank" rel="nofollow">ICSDroid</a>
                (Die App kostet im Play Store Geld, in F-Droid ist sie kostenlos verfügbar. Wenn keine der beiden
                Optionen für dich infrage kommt, kannst du den Kalender auch über einen Google Kalender importieren,
                siehe unten.)
            </li>
            <li>Öffne die App, nachdem die Installation abgeschlossen ist</li>
            <li>Tippe unten rechts auf das Plus-Icon</li>
            <li>Gib im neuen Dialog die URL <code>{{ route('caldav.calendar.internal') }}</code> ein</li>
            <li>Bestätige oben rechts mit dem Haken</li>
            <li>Fertig! Es kann etwas dauern, bis alle Termine aus dem Kalender sichtbar sind</li>
        </ol>

        <h3>Über Google Kalender</h3>

        <p>
            Es gibt auch die Möglichkeit, den Kalender über einen bestehenden Google Calender zu abonnieren. Das erspart
            die Installation von Apps, setzt aber voraus, dass du den Google Kalender auf deinem Gerät bereits
            eingerichtet hast.
        </p>
        <ol>
            <li>Im Web-Interface links unter „Weitere Kalender“ auf das Plus-Icon klicken, dann auf „Kalender
                abonnieren“
            </li>
            <li>Dann links im Menü „Kalender hinzufügen" und dann „Per URL" auswählen</li>
            <li>
                Als URL <code>{{ route('caldav.calendar.internal') }}</code> eingeben und auf den Button „Kalender
                hinzufügen“ klicken
            </li>
            <li>Fertig! Es kann etwas dauern, bis alle Termine aus dem Kalender sichtbar sind</li>
        </ol>


    </div>
</x-content>
