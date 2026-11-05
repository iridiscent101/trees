<?php

namespace App\Enums;

enum RoleName: string
{
    case Administrator = 'administrator';
    case CertifiedForester = 'certified-forester';
    case MaintenancePersonnel = 'maintenance-personnel';
    case AuthorizedStaff = 'authorized-staff';
    case DataEncoder = 'data-encoder';
    case User = 'user';
}
