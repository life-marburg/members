<?php

namespace App;

interface Rights
{
    public const P_EDIT_PAGES = 'edit pages';

    public const P_MANAGE_MEMBERS = 'manage life members';

    public const P_VIEW_ALL_INSTRUMENTS = 'view all instruments';

    public const P_DELETE_ACCOUNTS = 'delete user accounts';

    public const P_MANAGE_SHEETS = 'manage sheets';

    public const R_ADMIN = 'admin';
}
