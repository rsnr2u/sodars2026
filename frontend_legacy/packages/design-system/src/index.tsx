import React, { useEffect } from 'react';
import { usePermissionStore } from '@sodars/store';
import { identity } from '@sodars/auth';

// 1. Permission Gate
interface PermissionGateProps {
  can: string;
  children: React.ReactNode;
  fallback?: React.ReactNode;
}

export const PermissionGate: React.FC<PermissionGateProps> = ({
  can,
  children,
  fallback = null
}) => {
  usePermissionStore((state: any) => state.permissions);
  if (!identity.can(can)) {
    return <>{fallback}</>;
  }
  return <>{children}</>;
};

// 2. Premium Button
interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline' | 'danger' | 'success';
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
}

export const Button: React.FC<ButtonProps> = ({
  variant = 'primary',
  size = 'md',
  isLoading = false,
  children,
  className = '',
  disabled,
  ...props
}) => {
  const baseStyle = 'inline-flex items-center justify-center font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed';
  
  const sizes = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-4 py-2 text-sm',
    lg: 'px-5 py-2.5 text-base',
  };

  const variants = {
    primary: 'bg-primary hover:bg-primary-hover text-white shadow-sm border border-transparent',
    secondary: 'bg-secondary hover:bg-secondary-hover text-white shadow-sm border border-transparent',
    outline: 'border border-border bg-surface hover:bg-surface-hover text-text-primary',
    danger: 'bg-danger hover:bg-danger/90 text-white shadow-sm border border-transparent',
    success: 'bg-success hover:bg-success/90 text-white shadow-sm border border-transparent',
  };

  return (
    <button
      disabled={disabled || isLoading}
      className={`${baseStyle} ${sizes[size]} ${variants[variant]} ${className}`}
      {...props}
    >
      {isLoading && (
        <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
      )}
      {children}
    </button>
  );
};

// 3. Status Badge
interface BadgeProps {
  variant?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger' | 'info';
  children: React.ReactNode;
  className?: string;
  pulse?: boolean;
}

export const Badge: React.FC<BadgeProps> = ({
  variant = 'primary',
  children,
  className = '',
  pulse = false
}) => {
  const baseStyle = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold';
  
  const variants = {
    primary: 'bg-primary/10 text-primary border border-primary/20',
    secondary: 'bg-secondary/10 text-secondary border border-secondary/20',
    success: 'bg-success/10 text-success border border-success/20',
    warning: 'bg-warning/10 text-warning border border-warning/20',
    danger: 'bg-danger/10 text-danger border border-danger/20',
    info: 'bg-info/10 text-info border border-info/20',
  };

  return (
    <span className={`${baseStyle} ${variants[variant]} ${className}`}>
      {pulse && (
        <span className="relative flex h-1.5 w-1.5 mr-1.5">
          <span className={`animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ${
            variant === 'success' ? 'bg-success' : variant === 'warning' ? 'bg-warning' : variant === 'danger' ? 'bg-danger' : 'bg-primary'
          }`}></span>
          <span className={`relative inline-flex rounded-full h-1.5 w-1.5 ${
            variant === 'success' ? 'bg-success' : variant === 'warning' ? 'bg-warning' : variant === 'danger' ? 'bg-danger' : 'bg-primary'
          }`}></span>
        </span>
      )}
      {children}
    </span>
  );
};

// 4. Content Card
interface CardProps {
  children: React.ReactNode;
  className?: string;
  hoverable?: boolean;
  onClick?: () => void;
}

export const Card: React.FC<CardProps> = ({
  children,
  className = '',
  hoverable = false,
  onClick
}) => {
  return (
    <div 
      onClick={onClick}
      className={`bg-surface border border-border rounded-lg shadow-sm overflow-hidden transition-all duration-200 ${
        hoverable ? 'hover:shadow-md hover:border-border/80 hover:-translate-y-0.5 cursor-pointer' : ''
      } ${className}`}
    >
      {children}
    </div>
  );
};

// 5. Styled Form Input
interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helperText?: string;
}

export const Input: React.FC<InputProps> = ({
  label,
  error,
  helperText,
  className = '',
  id,
  ...props
}) => {
  const inputId = id || React.useId();
  return (
    <div className="w-full flex flex-col gap-1.5">
      {label && (
        <label htmlFor={inputId} className="text-xs font-semibold text-text-secondary select-none">
          {label}
        </label>
      )}
      <input
        id={inputId}
        className={`w-full px-3 py-2 text-sm bg-surface border rounded-md shadow-sm transition-all focus:outline-none focus:ring-1 focus:ring-primary ${
          error ? 'border-danger focus:ring-danger' : 'border-border focus:ring-primary'
        } ${className}`}
        {...props}
      />
      {error && <span className="text-[11px] font-medium text-danger">{error}</span>}
      {!error && helperText && <span className="text-[11px] text-text-muted">{helperText}</span>}
    </div>
  );
};

// 6. Styled Select Input
interface SelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  error?: string;
  options: Array<{ value: string; label: string }>;
}

export const Select: React.FC<SelectProps> = ({
  label,
  error,
  options,
  className = '',
  id,
  ...props
}) => {
  const selectId = id || React.useId();
  return (
    <div className="w-full flex flex-col gap-1.5">
      {label && (
        <label htmlFor={selectId} className="text-xs font-semibold text-text-secondary select-none">
          {label}
        </label>
      )}
      <select
        id={selectId}
        className={`w-full px-3 py-2 text-sm bg-surface border rounded-md shadow-sm transition-all focus:outline-none focus:ring-1 focus:ring-primary ${
          error ? 'border-danger focus:ring-danger' : 'border-border focus:ring-primary'
        } ${className}`}
        {...props}
      >
        {options.map((opt) => (
          <option key={opt.value} value={opt.value}>
            {opt.label}
          </option>
        ))}
      </select>
      {error && <span className="text-[11px] font-medium text-danger">{error}</span>}
    </div>
  );
};

// 7. Stepper / Slide-Over Drawer
interface DrawerProps {
  isOpen: boolean;
  onClose: () => void;
  title: string;
  children: React.ReactNode;
  footer?: React.ReactNode;
}

export const Drawer: React.FC<DrawerProps> = ({
  isOpen,
  onClose,
  title,
  children,
  footer
}) => {
  useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose();
    };
    if (isOpen) {
      document.body.style.overflow = 'hidden';
      window.addEventListener('keydown', handleEscape);
    }
    return () => {
      document.body.style.overflow = '';
      window.removeEventListener('keydown', handleEscape);
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
      <div className="absolute inset-0 overflow-hidden">
        {/* Backdrop */}
        <div 
          className="absolute inset-0 bg-black/45 backdrop-blur-sm transition-opacity" 
          onClick={onClose}
        />

        {/* Panel wrapper */}
        <div className="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
          <div className="pointer-events-auto w-screen max-w-lg transform bg-surface shadow-2xl transition-all duration-300 ease-in-out">
            <div className="flex h-full flex-col divide-y divide-border">
              {/* Header */}
              <div className="px-6 py-4 flex items-center justify-between">
                <h2 className="text-lg font-bold text-text-primary">{title}</h2>
                <button
                  type="button"
                  onClick={onClose}
                  className="rounded-md text-text-muted hover:text-text-primary focus:outline-none"
                  aria-label="Close panel"
                >
                  <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              {/* Content body */}
              <div className="flex-1 overflow-y-auto px-6 py-6">
                {children}
              </div>

              {/* Footer */}
              {footer && (
                <div className="px-6 py-4 bg-background">
                  {footer}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

// 8. Custom Dialog Modal
interface DialogProps {
  isOpen: boolean;
  onClose: () => void;
  title: string;
  children: React.ReactNode;
  confirmLabel?: string;
  onConfirm?: () => void;
  confirmVariant?: 'primary' | 'danger' | 'success';
}

export const Dialog: React.FC<DialogProps> = ({
  isOpen,
  onClose,
  title,
  children,
  confirmLabel = 'Confirm',
  onConfirm,
  confirmVariant = 'primary'
}) => {
  useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose();
    };
    if (isOpen) {
      window.addEventListener('keydown', handleEscape);
    }
    return () => {
      window.removeEventListener('keydown', handleEscape);
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
      {/* Backdrop */}
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />

      {/* Box */}
      <div className="relative bg-surface rounded-lg max-w-md w-full shadow-xl border border-border p-6 flex flex-col gap-4 z-10">
        <div className="flex items-center justify-between">
          <h3 className="text-base font-bold text-text-primary">{title}</h3>
          <button onClick={onClose} className="text-text-muted hover:text-text-primary focus:outline-none">
            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div className="text-sm text-text-secondary">{children}</div>
        <div className="flex justify-end gap-2.5 mt-2">
          <Button variant="outline" size="sm" onClick={onClose}>
            Cancel
          </Button>
          {onConfirm && (
            <Button variant={confirmVariant} size="sm" onClick={onConfirm}>
              {confirmLabel}
            </Button>
          )}
        </div>
      </div>
    </div>
  );
};

// 9. Standardized loading shimmer
interface SkeletonProps {
  className?: string;
  variant?: 'text' | 'rect' | 'circle';
}

export const Skeleton: React.FC<SkeletonProps> = ({
  className = '',
  variant = 'rect'
}) => {
  const base = 'animate-pulse bg-border-muted';
  const styles = {
    text: 'h-4 w-3/4 rounded',
    rect: 'h-32 w-full rounded-md',
    circle: 'h-12 w-12 rounded-full',
  };

  return <div className={`${base} ${styles[variant]} ${className}`} />;
};

// 10. Interactive Empty State
interface EmptyStateProps {
  title: string;
  description: string;
  icon?: React.ReactNode;
  actionLabel?: string;
  onAction?: () => void;
}

export const EmptyState: React.FC<EmptyStateProps> = ({
  title,
  description,
  icon,
  actionLabel,
  onAction
}) => {
  return (
    <div className="flex flex-col items-center justify-center p-8 text-center border border-dashed border-border rounded-lg bg-surface/50 gap-4">
      {icon ? (
        <div className="text-text-muted">{icon}</div>
      ) : (
        <svg className="h-10 w-10 text-text-muted" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.008 1.24l.885 1.77a2.25 2.25 0 002.007 1.24h1.98a2.25 2.25 0 002.007-1.24l.885-1.77a2.25 2.25 0 012.007-1.24h3.86m-18 0h18" />
        </svg>
      )}
      <div className="flex flex-col gap-1">
        <h3 className="text-sm font-bold text-text-primary">{title}</h3>
        <p className="text-xs text-text-secondary max-w-sm">{description}</p>
      </div>
      {actionLabel && onAction && (
        <Button variant="primary" size="sm" onClick={onAction}>
          {actionLabel}
        </Button>
      )}
    </div>
  );
};
