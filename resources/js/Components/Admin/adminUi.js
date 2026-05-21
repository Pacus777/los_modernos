/**
 * Clases compartidas del panel admin WAYNA (listados, formularios, botones).
 * Paleta: #f07e26 (wayna-500) · base #E8E6E0 (surface)
 */

export const adminInputClass = 'input-wayna w-full';

export const adminSelectClass = adminInputClass;

export const adminTextareaClass = 'input-wayna min-h-[120px] w-full';

export const adminLabelUpper = 'section-heading';

export const adminLabelField = 'block text-sm font-semibold text-stone-800';

/** Espaciado vertical entre campos del mismo paso. */
export const adminFormStack = 'flex flex-col gap-5';

/** Dos columnas en pantallas medianas (nombre/apellido, etc.). */
export const adminFormGrid2 = 'grid grid-cols-1 gap-5 sm:grid-cols-2';

/** Asterisco de campo obligatorio (usar dentro del label). */
export const adminLabelRequiredMark = 'ml-0.5 font-bold text-wayna-600';

export const adminSectionCard =
    'rounded-2xl border border-wayna-100 bg-surface-card/90 p-5 shadow-sm sm:p-6';

export const adminBackdropShort =
    'pointer-events-none absolute inset-x-0 top-0 h-48 bg-gradient-to-b from-surface-muted/90 via-surface/40 to-transparent';

export const adminBackdropTall =
    'pointer-events-none absolute inset-x-0 top-0 h-56 bg-gradient-to-b from-surface-muted via-surface/50 to-transparent';

export const adminListCardOuter =
    'overflow-hidden rounded-3xl border border-wayna-200/90 bg-surface-card shadow-xl shadow-wayna-900/[0.06] ring-1 ring-black/[0.03]';

export const adminListCardHeader =
    'border-b border-wayna-100 bg-gradient-to-r from-wayna-50/90 via-surface-card to-surface-muted px-6 py-5 sm:px-8';

export const adminTableHeadRow =
    'bg-gradient-to-r from-surface-muted to-wayna-50/50 text-left text-[11px] font-bold uppercase tracking-wider text-wayna-800/90';

export const adminTableRowHover =
    'transition-colors hover:bg-gradient-to-r hover:from-wayna-50/60 hover:to-transparent';

export const adminPrimaryGradientBtn = 'btn-wayna-primary-gradient';

export const adminFormFooterPrimaryBtn = 'btn-wayna-primary';

export const adminFormFooterSecondaryBtn = 'btn-wayna-secondary';

export const adminTableActionEdit =
    'rounded-xl border border-wayna-200 bg-surface-card px-3 py-1.5 text-xs font-bold text-wayna-800 shadow-sm transition hover:border-wayna-300 hover:bg-wayna-50 sm:text-sm';

export const adminTableActionDanger =
    'rounded-xl border border-red-200 bg-surface-card px-3 py-1.5 text-xs font-bold text-red-700 shadow-sm transition hover:bg-red-50 sm:text-sm';

export const adminTableActionCredenciales =
    'rounded-xl border border-amber-200 bg-amber-50/80 px-3 py-1.5 text-xs font-bold text-amber-950 shadow-sm transition hover:border-amber-300 hover:bg-amber-100 sm:text-sm';

export const adminPaginationBtnActive =
    'rounded-xl px-3 py-1.5 text-sm font-semibold bg-wayna-500 text-white shadow-md shadow-wayna-600/25';

export const adminPaginationBtnIdle =
    'rounded-xl border border-wayna-200/80 bg-surface-card px-3 py-1.5 text-sm font-semibold text-wayna-900 transition hover:border-wayna-300 hover:bg-wayna-50';

export const adminFileInputClass =
    'block w-full text-sm text-stone-700 file:mr-4 file:rounded-2xl file:border-0 file:bg-gradient-to-r file:from-wayna-50 file:to-surface-muted file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-wayna-800 hover:file:from-wayna-100 hover:file:to-wayna-50';

/** Contenedor con borde para prefijo monetario (Bs). */
export const adminInputMoneyWrap = 'relative w-full';

/**
 * Modales Wayna: el panel limita altura (Modal.jsx); el cuerpo hace scroll
 * y cabecera/pie quedan visibles.
 */
export const modalWaynaShell =
    'flex min-h-0 max-h-full w-full flex-col overflow-hidden bg-white';

export const modalWaynaBody =
    'min-h-0 flex-1 overflow-y-auto overscroll-contain bg-surface-card';

/** Alias del cuerpo scrollable (fichas, listados). */
export const modalWaynaBodyCompact = modalWaynaBody;

export const modalWaynaHeader = 'header-wayna-gradient shrink-0 px-4 py-3 sm:px-5';

export const modalWaynaFooter =
    'shrink-0 border-t border-wayna-100 bg-white px-4 py-3 sm:px-5';
