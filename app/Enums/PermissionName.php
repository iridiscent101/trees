<?php

namespace App\Enums;

enum PermissionName: string
{
    case AccessPanel = 'panel.access';
    case ViewDashboard = 'dashboard.view';
    case ViewTrees = 'trees.view';
    case CreateTrees = 'trees.create';
    case UpdateTrees = 'trees.update';
    case ArchiveTrees = 'trees.archive';
    case RestoreTrees = 'trees.restore';
    case ViewMonitoring = 'monitoring.view';
    case CreateMonitoring = 'monitoring.create';
    case VerifyMonitoring = 'monitoring.verify';
    case UploadPhotos = 'photos.upload';
    case ManageSpecies = 'species.manage';
    case ManageCampusAreas = 'campus-areas.manage';
    case ManageVerifications = 'verifications.manage';
    case ManageQrCodes = 'qr-codes.manage';
    case ViewReports = 'reports.view';
    case ManageUsers = 'users.manage';
    case ManageRoles = 'roles.manage';
    case ViewAuditLogs = 'audit-logs.view';
    case ManageBackups = 'backups.manage';
}
