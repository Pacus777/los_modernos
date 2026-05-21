<?php

namespace App\Enums;

/**
 * Acciones críticas registradas en audit_logs (S3-09).
 */
enum AuditAction: string
{
    case AuthLoginSuccess = 'auth.login.success';
    case AuthLoginFailed = 'auth.login.failed';
    case AuthLogout = 'auth.logout';
    case AuthTwoFactorPassed = 'auth.two_factor.passed';

    case AdminTwoFactorEnabled = 'admin.two_factor.enabled';
    case AdminTwoFactorDisabled = 'admin.two_factor.disabled';

    case AdminDonationValidated = 'admin.donation.validated';
    case AdminDonationRejected = 'admin.donation.rejected';
    case AdminDonationMassReview = 'admin.donation.mass_review';

    case CajeroDonationCashConfirmed = 'cajero.donation.cash_confirmed';

    case AdminEntrepreneurCreated = 'admin.entrepreneur.created';
    case AdminEntrepreneurUpdated = 'admin.entrepreneur.updated';
    case AdminEntrepreneurDeactivated = 'admin.entrepreneur.deactivated';
    case AdminEntrepreneurAccountCreated = 'admin.entrepreneur.account_created';
    case AdminEntrepreneurCredentialsResent = 'admin.entrepreneur.credentials_resent';

    case EmprendedorPasswordChanged = 'emprendedor.password.changed';
    case EmprendedorProfileUpdated = 'emprendedor.profile.updated';
    case EmprendedorDonationsExported = 'emprendedor.donations.exported';
    case EmprendedorMetaCreated = 'emprendedor.meta.created';
    case EmprendedorMetaUpdated = 'emprendedor.meta.updated';
    case EmprendedorMetaClosed = 'emprendedor.meta.closed';
}
