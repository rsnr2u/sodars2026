import { useMemo } from 'react';
import { EnterpriseDataGrid, ColumnDef } from '@sodars/datagrid';
import { Provider } from '@sodars/models';
import { MockEngine } from '@sodars/mock-data';

export function ProvidersDirectory() {
  // Load mock Indian provider records using the deterministic seed engine
  const providers = useMemo(() => MockEngine.getProviders(500), []);

  const columns: ColumnDef<Provider>[] = useMemo(() => [
    {
      id: 'name',
      header: 'Provider Name',
      sortValue: (row: Provider) => row.name,
      searchValue: (row: Provider) => row.name,
      accessor: (row: Provider) => (
        <div className="flex flex-col">
          <span className="font-semibold text-text-primary hover:text-primary transition-colors">
            {row.name}
          </span>
          <span className="text-xs text-text-muted mt-0.5">{row.id}</span>
        </div>
      )
    },
    {
      id: 'gstNumber',
      header: 'GSTIN / PAN',
      searchValue: (row: Provider) => `${row.gstNumber} ${row.phone}`,
      accessor: (row: Provider) => (
        <div className="flex flex-col font-mono text-xs text-text-secondary">
          <span>{row.gstNumber || '27APCPA1234P1Z5'}</span>
          <span className="text-[10px] text-text-muted mt-0.5">{row.phone}</span>
        </div>
      )
    },
    {
      id: 'location',
      header: 'Location',
      sortValue: (row: Provider) => row.city,
      searchValue: (row: Provider) => `${row.city} ${row.state}`,
      accessor: (row: Provider) => (
        <div className="flex flex-col text-sm text-text-secondary">
          <span className="font-medium">{row.city}</span>
          <span className="text-xs text-text-muted">{row.state}</span>
        </div>
      )
    },
    {
      id: 'status',
      header: 'Verification Status',
      sortValue: (row: Provider) => row.status,
      searchValue: (row: Provider) => row.status,
      accessor: (row: Provider) => {
        const colors = {
          Verified: 'bg-success-light text-success border-success/20',
          'Under Review': 'bg-gold-light text-darkGold border-gold/20',
          Pending: 'bg-slate-light text-slate border-slate/20',
          Suspended: 'bg-danger-light text-danger border-danger/20'
        };
        return (
          <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ${colors[row.status]}`}>
            {row.status}
          </span>
        );
      }
    },
    {
      id: 'compliance',
      header: 'Compliance Status',
      sortValue: (row: Provider) => row.complianceStatus,
      searchValue: (row: Provider) => row.complianceStatus,
      accessor: (row: Provider) => {
        const colors = {
          Compliant: 'bg-success-light text-success border-success/20',
          Pending: 'bg-slate-light text-slate border-slate/20',
          Expired: 'bg-danger-light text-danger border-danger/20'
        };
        return (
          <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ${colors[row.complianceStatus]}`}>
            {row.complianceStatus}
          </span>
        );
      }
    },
    {
      id: 'branchesCount',
      header: 'Branches / Staff',
      sortValue: (row: Provider) => row.branches.length,
      accessor: (row: Provider) => (
        <div className="flex items-center gap-1.5 text-sm text-text-secondary">
          <span className="font-semibold text-text-primary">{row.branches.length}</span>
          <span className="text-text-muted">/</span>
          <span className="font-semibold text-text-primary">{row.staff.length}</span>
        </div>
      )
    },
    {
      id: 'agreements',
      header: 'Agreements',
      sortValue: (row: Provider) => row.agreements.length,
      accessor: (row: Provider) => (
        <span className="inline-flex items-center gap-1 text-sm font-medium text-text-secondary">
          {row.agreements.length} Active
        </span>
      )
    },
    {
      id: 'createdDate',
      header: 'Created On',
      sortValue: (row: Provider) => row.createdDate,
      accessor: (row: Provider) => (
        <span className="text-sm text-text-secondary font-medium">
          {row.createdDate}
        </span>
      )
    }
  ], []);

  const bulkActions = useMemo(() => [
    {
      label: 'Verify Selected',
      onClick: (selected: Provider[]) => {
        alert(`Verifying ${selected.length} providers`);
      }
    },
    {
      label: 'Suspend Selected',
      variant: 'danger' as const,
      onClick: (selected: Provider[]) => {
        alert(`Suspending ${selected.length} providers`);
      }
    }
  ], []);

  return (
    <div className="flex flex-col gap-6 h-full">
      {/* Page Header (Pattern level wrapper) */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-border pb-5">
        <div>
          <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-text-muted mb-1">
            <span>Workspaces</span>
            <span>/</span>
            <span>Providers Workspace</span>
          </div>
          <h1 className="text-2xl font-extrabold tracking-tight text-text-primary font-heading">
            Providers Directory
          </h1>
          <p className="text-sm text-text-secondary mt-1">
            Manage vendor compliance records, Indian outdoor advertising aggregates, and media leases.
          </p>
        </div>

        {/* Primary CTA (Aligned to Rule 3 constraints) */}
        <button
          onClick={() => alert('Wizard creation template workflow placeholder')}
          className="flex items-center gap-1.5 bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-md text-sm font-semibold shadow-sm transition-colors cursor-pointer"
        >
          Add Provider
        </button>
      </div>

      {/* Main Enterprise DataGrid List container */}
      <div className="flex-1 min-h-0">
        <EnterpriseDataGrid
          data={providers}
          columns={columns}
          bulkActions={bulkActions}
          searchPlaceholder="Search providers by name, GSTIN, city, state..."
        />
      </div>
    </div>
  );
}
export default ProvidersDirectory;
