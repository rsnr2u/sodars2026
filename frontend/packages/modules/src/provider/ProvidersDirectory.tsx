import { useState, useMemo } from 'react';
import { EnterpriseDataGrid, ColumnDef } from '@sodars/datagrid';
import { Provider } from '@sodars/models';
import { MockEngine } from '@sodars/mock-data';
import { NavigationIcons, StatusIcons, FileIcons } from '@sodars/icons';

export function ProvidersDirectory() {
  const [selectedProvider, setSelectedProvider] = useState<Provider | null>(null);
  const [activeTab, setActiveTab] = useState<'overview' | 'verification' | 'compliance' | 'documents' | 'agreements' | 'staff' | 'branches' | 'analytics' | 'settings'>('overview');
  
  // Load mock Indian provider records
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
          <span>{row.gstNumber}</span>
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

  // Return to Directory
  if (!selectedProvider) {
    return (
      <div className="flex flex-col gap-6 h-full">
        {/* Page Header */}
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

          <button
            onClick={() => alert('Wizard creation template workflow placeholder')}
            className="flex items-center gap-1.5 bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-md text-sm font-semibold shadow-sm transition-colors cursor-pointer"
          >
            Add Provider
          </button>
        </div>

        {/* Directory DataGrid */}
        <div className="flex-1 min-h-0">
          <EnterpriseDataGrid
            data={providers}
            columns={columns}
            bulkActions={bulkActions}
            onRowClick={(row) => setSelectedProvider(row)}
            searchPlaceholder="Search providers by name, GSTIN, city, state..."
          />
        </div>
      </div>
    );
  }

  // Provider Detail Workspace Layout
  return (
    <div className="flex flex-col gap-6 h-full">
      {/* Workspace Header */}
      <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-border pb-5">
        <div>
          <button
            onClick={() => setSelectedProvider(null)}
            className="flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-primary-hover transition-colors mb-2 cursor-pointer"
          >
            <NavigationIcons.ChevronLeft className="h-4 w-4" />
            Back to Providers Directory
          </button>
          <div className="flex items-center gap-3">
            <h1 className="text-2xl font-extrabold tracking-tight text-text-primary font-heading">
              {selectedProvider.name}
            </h1>
            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ${
              selectedProvider.status === 'Verified' ? 'bg-success-light text-success border-success/20' : 'bg-gold-light text-darkGold border-gold/20'
            }`}>
              {selectedProvider.status}
            </span>
            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ${
              selectedProvider.complianceStatus === 'Compliant' ? 'bg-success-light text-success border-success/20' : 'bg-danger-light text-danger border-danger/20'
            }`}>
              {selectedProvider.complianceStatus}
            </span>
          </div>
          <p className="text-sm text-text-secondary mt-1">
            Aggregate ID: {selectedProvider.id} • Registered GSTIN: {selectedProvider.gstNumber} • Region: {selectedProvider.city}, {selectedProvider.state}
          </p>
        </div>
      </div>

      {/* Tabs Navigation */}
      <div className="border-b border-border">
        <nav className="flex flex-wrap -mb-px gap-6">
          {(['overview', 'verification', 'compliance', 'documents', 'agreements', 'staff', 'branches', 'analytics', 'settings'] as const).map(tab => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`pb-4 text-sm font-semibold capitalize border-b-2 transition-colors cursor-pointer ${
                activeTab === tab
                  ? 'border-primary text-primary'
                  : 'border-transparent text-text-muted hover:text-text-primary hover:border-border'
              }`}
            >
              {tab}
            </button>
          ))}
        </nav>
      </div>

      {/* Dynamic Tab Body */}
      <div className="flex-1 min-h-[400px]">
        {activeTab === 'overview' && (
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {/* Stats Overview */}
            <div className="border border-border rounded-lg p-5 bg-surface shadow-xs">
              <div className="text-xs font-bold uppercase tracking-wider text-text-muted mb-1">Total Branches</div>
              <div className="text-3xl font-extrabold text-text-primary mb-2">{selectedProvider.branches.length}</div>
              <div className="text-xs text-text-muted">Active branches across state division offices.</div>
            </div>
            <div className="border border-border rounded-lg p-5 bg-surface shadow-xs">
              <div className="text-xs font-bold uppercase tracking-wider text-text-muted mb-1">Roster Count</div>
              <div className="text-3xl font-extrabold text-text-primary mb-2">{selectedProvider.staff.length} Employees</div>
              <div className="text-xs text-text-muted">Registered administrative and operations team.</div>
            </div>
            <div className="border border-border rounded-lg p-5 bg-surface shadow-xs">
              <div className="text-xs font-bold uppercase tracking-wider text-text-muted mb-1">Active Leases</div>
              <div className="text-3xl font-extrabold text-text-primary mb-2">{selectedProvider.agreements.length} Contracts</div>
              <div className="text-xs text-text-muted">Legally signed standard lease formats.</div>
            </div>
            {/* Quick Info Card */}
            <div className="md:col-span-2 border border-border rounded-lg p-6 bg-surface shadow-xs">
              <h3 className="font-heading text-lg font-bold text-text-primary mb-4">Operations Summary</h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                  <span className="text-text-muted block text-xs uppercase font-bold">Email Address</span>
                  <span className="text-text-secondary font-medium">{selectedProvider.email}</span>
                </div>
                <div>
                  <span className="text-text-muted block text-xs uppercase font-bold">Mobile Line</span>
                  <span className="text-text-secondary font-medium">{selectedProvider.phone}</span>
                </div>
                <div>
                  <span className="text-text-muted block text-xs uppercase font-bold">Onboarded Since</span>
                  <span className="text-text-secondary font-medium">{selectedProvider.createdDate}</span>
                </div>
                <div>
                  <span className="text-text-muted block text-xs uppercase font-bold">Auditor Review Status</span>
                  <span className="text-text-secondary font-medium">Passed Pre-checks</span>
                </div>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'verification' && (
          <div className="border border-border rounded-lg p-6 bg-surface shadow-xs space-y-6">
            <div>
              <h3 className="font-heading text-lg font-bold text-text-primary mb-2">Auditor Verification Board</h3>
              <p className="text-sm text-text-secondary">Verify vendor certificates, perform on-site billboard checks, and confirm GST status.</p>
            </div>
            <div className="border border-border rounded-lg divide-y divide-border text-sm text-text-secondary">
              <div className="flex items-center justify-between p-4 bg-background">
                <div className="flex items-center gap-3">
                  <StatusIcons.Success className="h-5 w-5 text-success" />
                  <span>GST registration matched on government lookup database</span>
                </div>
                <span className="text-xs font-semibold text-success">Verified</span>
              </div>
              <div className="flex items-center justify-between p-4 bg-background">
                <div className="flex items-center gap-3">
                  <StatusIcons.Success className="h-5 w-5 text-success" />
                  <span>PAN card document validation check passed</span>
                </div>
                <span className="text-xs font-semibold text-success">Verified</span>
              </div>
              <div className="flex items-center justify-between p-4 bg-background">
                <div className="flex items-center gap-3">
                  <StatusIcons.Clock className="h-5 w-5 text-darkGold" />
                  <span>Physical check of primary branches site holdings</span>
                </div>
                <span className="text-xs font-semibold text-darkGold">In Progress</span>
              </div>
            </div>
            <div className="flex justify-end gap-3 pt-4">
              <button className="px-4 py-2 border border-border text-text-secondary hover:bg-surface-hover hover:text-text-primary rounded-md text-sm font-semibold cursor-pointer">
                Request Re-audit
              </button>
              <button className="px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-md text-sm font-semibold cursor-pointer">
                Approve Vendor Verification
              </button>
            </div>
          </div>
        )}

        {activeTab === 'compliance' && (
          <div className="border border-border rounded-lg p-6 bg-surface shadow-xs space-y-6">
            <div>
              <h3 className="font-heading text-lg font-bold text-text-primary mb-1">Corporate Tax Compliance Logs</h3>
              <p className="text-sm text-text-secondary">Validate Indian GST state declarations tax filings.</p>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
              <div className="p-4 border border-border rounded-lg bg-background">
                <span className="text-text-muted block text-xs uppercase font-bold mb-1">GSTIN Number</span>
                <span className="font-mono font-bold text-text-primary">{selectedProvider.gstNumber}</span>
              </div>
              <div className="p-4 border border-border rounded-lg bg-background">
                <span className="text-text-muted block text-xs uppercase font-bold mb-1">PAN Card</span>
                <span className="font-mono font-bold text-text-primary">{selectedProvider.gstNumber.substring(2, 12)}</span>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'documents' && (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div className="border border-border rounded-lg p-4 bg-surface flex items-start gap-4 hover:shadow-sm transition-all">
              <FileIcons.Pdf className="h-10 w-10 text-danger flex-shrink-0" />
              <div className="flex-1 min-w-0">
                <div className="font-bold text-sm text-text-primary truncate">GST Certificate</div>
                <div className="text-xs text-text-muted mt-1">Uploaded: {selectedProvider.createdDate}</div>
              </div>
            </div>
            <div className="border border-border rounded-lg p-4 bg-surface flex items-start gap-4 hover:shadow-sm transition-all">
              <FileIcons.Pdf className="h-10 w-10 text-danger flex-shrink-0" />
              <div className="flex-1 min-w-0">
                <div className="font-bold text-sm text-text-primary truncate">PAN Copy</div>
                <div className="text-xs text-text-muted mt-1">Uploaded: {selectedProvider.createdDate}</div>
              </div>
            </div>
            <div className="border border-border rounded-lg p-4 bg-surface flex items-start gap-4 hover:shadow-sm transition-all">
              <FileIcons.Pdf className="h-10 w-10 text-danger flex-shrink-0" />
              <div className="flex-1 min-w-0">
                <div className="font-bold text-sm text-text-primary truncate">Trade License</div>
                <div className="text-xs text-text-muted mt-1">Uploaded: {selectedProvider.createdDate}</div>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'agreements' && (
          <div className="border border-border rounded-lg bg-surface shadow-xs overflow-hidden">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-background border-b border-border">
                  <th className="py-3 px-4 text-xs font-semibold text-text-muted uppercase">Agreement Title</th>
                  <th className="py-3 px-4 text-xs font-semibold text-text-muted uppercase">Status</th>
                </tr>
              </thead>
              <tbody>
                {selectedProvider.agreements.map((agr, index) => (
                  <tr key={index} className="border-b border-border hover:bg-surface-hover transition-colors">
                    <td className="py-3 px-4 text-sm font-semibold text-text-primary">{agr.title}</td>
                    <td className="py-3 px-4 text-sm">
                      <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ${
                        agr.status === 'Active' ? 'bg-success-light text-success border-success/20' : 'bg-danger-light text-danger border-danger/20'
                      }`}>
                        {agr.status}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {activeTab === 'staff' && (
          <div className="border border-border rounded-lg bg-surface shadow-xs overflow-hidden">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-background border-b border-border">
                  <th className="py-3 px-4 text-xs font-semibold text-text-muted uppercase">Staff Name</th>
                  <th className="py-3 px-4 text-xs font-semibold text-text-muted uppercase">Role</th>
                  <th className="py-3 px-4 text-xs font-semibold text-text-muted uppercase">Email / Mobile</th>
                </tr>
              </thead>
              <tbody>
                {selectedProvider.staff.map((stf, index) => (
                  <tr key={index} className="border-b border-border hover:bg-surface-hover transition-colors">
                    <td className="py-3 px-4 text-sm font-semibold text-text-primary">{stf.name}</td>
                    <td className="py-3 px-4 text-sm text-text-secondary">{stf.role}</td>
                    <td className="py-3 px-4 text-sm text-text-muted font-mono">
                      {stf.email} <br /> {stf.phone}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {activeTab === 'branches' && (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {selectedProvider.branches.map((brn, index) => (
              <div key={index} className="border border-border rounded-lg p-5 bg-surface shadow-xs hover:border-primary/20 transition-all">
                <div className="flex items-center gap-2 mb-2">
                  <span className="p-1.5 rounded-md bg-primary-light text-primary">
                    <NavigationIcons.Menu className="h-4 w-4" />
                  </span>
                  <h4 className="font-bold text-text-primary text-sm">{brn.name}</h4>
                </div>
                <div className="text-xs text-text-muted">
                  Office Location: {brn.city}, {brn.state} <br />
                  Office Status: <span className="font-semibold text-success">{brn.status}</span>
                </div>
              </div>
            ))}
          </div>
        )}

        {activeTab === 'analytics' && (
          <div className="border border-border rounded-lg p-6 bg-surface shadow-xs text-center space-y-4">
            <h3 className="font-heading text-lg font-bold text-text-primary">Regional Operations Yields</h3>
            <p className="text-sm text-text-muted">Chart placeholder rendering yield metrics for {selectedProvider.city} region.</p>
            <div className="h-48 border border-border border-dashed rounded-lg flex items-center justify-center bg-background text-text-muted font-mono text-xs">
              [Visual Analytics Chart Block]
            </div>
          </div>
        )}

        {activeTab === 'settings' && (
          <div className="border border-border rounded-lg p-6 bg-surface shadow-xs space-y-6">
            <div>
              <h3 className="font-heading text-lg font-bold text-text-primary mb-2">Workspace settings</h3>
              <p className="text-sm text-text-secondary">Configure provider permissions, webhook notifications, and document notifications.</p>
            </div>
            <div className="space-y-4">
              <label className="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" defaultChecked className="rounded border-border text-primary focus:ring-primary/20" />
                <span className="text-sm text-text-secondary font-medium">Enable automatic GST filings matching checks</span>
              </label>
              <label className="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" defaultChecked className="rounded border-border text-primary focus:ring-primary/20" />
                <span className="text-sm text-text-secondary font-medium">Send alarm triggers when documents expire</span>
              </label>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
export default ProvidersDirectory;
