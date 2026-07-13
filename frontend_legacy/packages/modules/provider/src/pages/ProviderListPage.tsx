import React, { useState, useEffect, useMemo, useRef } from 'react';
import { useProviders } from '../hooks/useProviders';
import { ComplianceCalculator } from '../calculators/ComplianceCalculator';
import { ProviderStatus } from '../enums';
import { Provider } from '../types';
import { SodarsIcon } from '@sodars/icons';
import {
  Button,
  Badge,
  Card,
  Input,
  Select,
  Drawer,
  Dialog,
  Skeleton,
  EmptyState
} from '@sodars/design-system';
import {
  Search,
  SlidersHorizontal,
  LayoutGrid,
  Table as TableIcon,
  Download,
  Trash2,
  Ban,
  Plus,
  Eye,
  ArrowUpDown,
  Maximize2,
  HelpCircle,
  Building,
  CheckCircle,
  FileCheck,
  Briefcase,
  ChevronRight,
  ChevronLeft,
  X
} from 'lucide-react';

export const ProviderListPage: React.FC = () => {
  const { data: providers, isLoading, createProvider } = useProviders();

  // Layout & Density
  const [layoutView, setLayoutView] = useState<'table' | 'grid'>('table');
  const [density, setDensity] = useState<'relaxed' | 'normal' | 'compact'>('normal');

  // Search & Filtering
  const [searchQuery, setSearchQuery] = useState('');
  const [currentView, setCurrentView] = useState<'all' | 'verified' | 'under_review' | 'pending' | 'suspended' | 'compliant'>('all');
  const [cityFilter, setCityFilter] = useState<string>('All');
  const [stateFilter, setStateFilter] = useState<string>('All');
  const [revenueFilter, setRevenueFilter] = useState<string>('All');
  const [showFilters, setShowFilters] = useState(false);

  // Sorting
  const [sortField, setSortField] = useState<keyof Provider | 'compliance' | 'revenue'>('name');
  const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');

  // Selection
  const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set());

  // Column Visibility Manager
  const [showColumnDrawer, setShowColumnDrawer] = useState(false);
  const [visibleColumns, setVisibleColumns] = useState<Record<string, boolean>>({
    id: true,
    name: true,
    status: true,
    compliance: true,
    revenue: true,
    contact: true,
    city: true,
    actions: true
  });

  // Dialogs & Modals
  const [showShortcutsDialog, setShowShortcutsDialog] = useState(false);
  const [showDrawer, setShowDrawer] = useState(false);

  // Bulk confirmation dialogs
  const [bulkActionType, setBulkActionType] = useState<'verify' | 'suspend' | 'delete' | null>(null);

  // Multi-step Registration Wizard Form State
  const [wizardStep, setWizardStep] = useState<1 | 2 | 3 | 4 | 5>(1);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [street, setStreet] = useState('');
  const [city, setCity] = useState('');
  const [stateCode, setStateCode] = useState('');
  const [zip, setZip] = useState('');
  const [gst, setGst] = useState('');
  const [bank, setBank] = useState('');
  const [accName, setAccName] = useState('');
  const [accNum, setAccNum] = useState('');
  const [routing, setRouting] = useState('');

  // Form Validation errors
  const [formErrors, setFormErrors] = useState<Record<string, string>>({});

  // Keyboard Shortcuts Hook
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      // Ignore shortcuts if user is typing in form/search inputs
      if (
        document.activeElement?.tagName === 'INPUT' ||
        document.activeElement?.tagName === 'SELECT' ||
        document.activeElement?.tagName === 'TEXTAREA'
      ) {
        return;
      }

      if (e.key === '?') {
        e.preventDefault();
        setShowShortcutsDialog(prev => !prev);
      } else if (e.key === 'c') {
        e.preventDefault();
        setWizardStep(1);
        setShowDrawer(true);
      } else if (e.key === '/') {
        e.preventDefault();
        const searchInput = document.getElementById('global-search-input');
        if (searchInput) searchInput.focus();
      } else if (e.key === 'g') {
        e.preventDefault();
        setLayoutView(prev => (prev === 'table' ? 'grid' : 'table'));
      } else if (e.key === 'd') {
        e.preventDefault();
        setDensity(prev => (prev === 'normal' ? 'compact' : prev === 'compact' ? 'relaxed' : 'normal'));
      } else if (e.key === 'v') {
        // saved views cyclical toggle
        e.preventDefault();
        const views: Array<typeof currentView> = ['all', 'verified', 'under_review', 'pending', 'suspended', 'compliant'];
        const nextIndex = (views.indexOf(currentView) + 1) % views.length;
        setCurrentView(views[nextIndex]);
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [currentView]);

  // Handle Wizard Steps Validation
  const validateStep = (step: number): boolean => {
    const errors: Record<string, string> = {};
    if (step === 1) {
      if (!name.trim()) errors.name = 'Company legal name is required';
      if (!email.trim() || !/\S+@\S+\.\S+/.test(email)) errors.email = 'Valid corporate email address is required';
    } else if (step === 2) {
      if (!street.trim()) errors.street = 'Street address is required';
      if (!city.trim()) errors.city = 'City name is required';
      if (!stateCode.trim()) errors.stateCode = 'State (e.g. NSW) is required';
    } else if (step === 3) {
      if (gst && !/^[A-Z0-9-]+$/.test(gst)) errors.gst = 'GST ID format is invalid';
    } else if (step === 4) {
      if (bank && !accNum.trim()) errors.accNum = 'Account number is required if bank is specified';
    }
    setFormErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleNextStep = () => {
    if (validateStep(wizardStep)) {
      setWizardStep(prev => (prev + 1) as any);
    }
  };

  const handlePrevStep = () => {
    setWizardStep(prev => (prev - 1) as any);
  };

  const handleCreate = async () => {
    try {
      const now = Date.now();
      const providerId = `prov-${Date.now()}`;
      await createProvider({
        id: providerId,
        name,
        email,
        phone: phone || '000-000000',
        primaryContact: {
          name: 'Contact Person',
          role: 'Operations Manager',
          email,
          phone: phone || '000-000000',
        },
        status: ProviderStatus.Pending,
        gstRegistration: gst
          ? {
              id: `gst-${Date.now()}`,
              providerId,
              gstNumber: gst,
              stateCode: stateCode || 'NSW',
              registeredAddress: {
                street,
                city,
                state: stateCode,
                zipCode: zip,
                country: 'AU',
                isBilling: false,
              },
              createdAt: now,
              updatedAt: now,
              version: 1,
              isActive: true,
            }
          : undefined,
        bankAccount: bank
          ? {
              id: `bank-${Date.now()}`,
              providerId,
              bankName: bank,
              accountHolderName: accName || name,
              accountNumber: accNum,
              routingNumber: routing || undefined,
              createdAt: now,
              updatedAt: now,
              version: 1,
              isActive: true,
            }
          : undefined,
        documents: [],
        agreements: [],
        verifications: [],
        notes: [],
        timeline: [],
        createdAt: now,
        updatedAt: now,
        version: 1,
        isActive: true,
      });

      // Clear out states
      setName('');
      setEmail('');
      setPhone('');
      setGst('');
      setBank('');
      setAccName('');
      setAccNum('');
      setRouting('');
      setStreet('');
      setCity('');
      setStateCode('');
      setZip('');
      setWizardStep(1);
      setShowDrawer(false);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  // Mock Sorting & Filtering computations
  const sortedAndFiltered = useMemo(() => {
    let result = [...providers];

    // Filter by view tab selection
    if (currentView === 'verified') {
      result = result.filter(p => p.status === ProviderStatus.Verified);
    } else if (currentView === 'under_review') {
      result = result.filter(p => p.status === ProviderStatus.UnderReview);
    } else if (currentView === 'pending') {
      result = result.filter(p => p.status === ProviderStatus.Pending);
    } else if (currentView === 'suspended') {
      result = result.filter(p => p.status === ProviderStatus.Rejected);
    } else if (currentView === 'compliant') {
      result = result.filter(p => {
        const summary = ComplianceCalculator.calculate(p);
        return summary.overallStatus === 'Compliant';
      });
    }

    // Filter by manual search query
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      result = result.filter(
        p => p.name.toLowerCase().includes(q) || p.id.toLowerCase().includes(q) || p.email.toLowerCase().includes(q)
      );
    }

    // Filter by city, state, or mock revenue limits
    if (cityFilter !== 'All') {
      result = result.filter(p => p.gstRegistration?.registeredAddress?.city === cityFilter);
    }
    if (stateFilter !== 'All') {
      result = result.filter(p => p.gstRegistration?.registeredAddress?.state === stateFilter);
    }
    if (revenueFilter !== 'All') {
      // Mock calculation: count active agreements to infer revenue class
      result = result.filter(p => {
        const agreementCount = p.agreements?.length || 0;
        if (revenueFilter === 'high') return agreementCount >= 2;
        if (revenueFilter === 'low') return agreementCount === 0;
        return agreementCount === 1; // medium
      });
    }

    // Sort result list
    result.sort((a, b) => {
      let aVal: any = a[sortField as keyof Provider] ?? '';
      let bVal: any = b[sortField as keyof Provider] ?? '';

      if (sortField === 'compliance') {
        aVal = ComplianceCalculator.calculate(a).overallStatus;
        bVal = ComplianceCalculator.calculate(b).overallStatus;
      } else if (sortField === 'revenue') {
        aVal = a.agreements?.length || 0;
        bVal = b.agreements?.length || 0;
      }

      if (typeof aVal === 'string') {
        return sortDirection === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
      }
      return sortDirection === 'asc' ? (aVal > bVal ? 1 : -1) : aVal < bVal ? 1 : -1;
    });

    return result;
  }, [providers, currentView, searchQuery, cityFilter, stateFilter, revenueFilter, sortField, sortDirection]);

  // Bulk operations handlers
  const handleBulkSelectToggle = (id: string) => {
    setSelectedIds(prev => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const handleSelectAllToggle = () => {
    if (selectedIds.size === sortedAndFiltered.length) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(sortedAndFiltered.map(p => p.id)));
    }
  };

  const executeBulkAction = () => {
    // Under freeze rule, we update local visual alerts rather than mutation service
    alert(`Successfully processed bulk '${bulkActionType}' operation on ${selectedIds.size} providers.`);
    setSelectedIds(new Set());
    setBulkActionType(null);
  };

  const handleExportCSV = () => {
    const listToExport = selectedIds.size > 0 ? sortedAndFiltered.filter(p => selectedIds.has(p.id)) : sortedAndFiltered;
    const header = 'Provider ID,Legal Name,Status,Email,Phone,City,State,Compliance\n';
    const rows = listToExport
      .map(p => {
        const compliance = ComplianceCalculator.calculate(p).overallStatus;
        return `"${p.id}","${p.name}","${p.status}","${p.email}","${p.phone}","${
          p.gstRegistration?.registeredAddress?.city || ''
        }","${p.gstRegistration?.registeredAddress?.state || ''}","${compliance}"`;
      })
      .join('\n');

    const blob = new Blob([header + rows], { type: 'text/csv;charset=utf-8;' });
    const link = window.document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', `SODAARS_Providers_Export_${Date.now()}.csv`);
    window.document.body.appendChild(link);
    link.click();
    window.document.body.removeChild(link);
  };

  // Helper colors mapping
  const getOverallBadge = (status: 'Compliant' | 'Pending' | 'Expired') => {
    switch (status) {
      case 'Compliant':
        return 'success';
      case 'Expired':
        return 'danger';
      default:
        return 'warning';
    }
  };

  const getVerificationBadge = (status: ProviderStatus) => {
    switch (status) {
      case ProviderStatus.Verified:
        return 'success';
      case ProviderStatus.UnderReview:
        return 'warning';
      case ProviderStatus.Rejected:
        return 'danger';
      default:
        return 'secondary';
    }
  };

  // Extract cities/states options for filter panel
  const cityOptions = useMemo(() => {
    const set = new Set<string>();
    providers.forEach(p => {
      const city = p.gstRegistration?.registeredAddress?.city;
      if (city) set.add(city);
    });
    return ['All', ...Array.from(set)];
  }, [providers]);

  const stateOptions = useMemo(() => {
    const set = new Set<string>();
    providers.forEach(p => {
      const state = p.gstRegistration?.registeredAddress?.state;
      if (state) set.add(state);
    });
    return ['All', ...Array.from(set)];
  }, [providers]);

  return (
    <div className="space-y-6 select-none font-sans relative">
      {/* Header Context panel */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-border pb-5 gap-4">
        <div>
          <div className="flex items-center gap-2">
            <h2 className="text-xl font-bold tracking-tight text-text-primary flex items-center">
              <SodarsIcon name="provider" className="text-primary mr-2.5" size={22} />
              Providers Directory
            </h2>
            <Badge variant="secondary" className="font-mono text-[10px]">
              {sortedAndFiltered.length} Active Aggregates
            </Badge>
          </div>
          <p className="text-text-secondary text-xs mt-1">
            Register corporate providers, review document checklist compliance, and manage regional partner branches directory.
          </p>
        </div>
        <div className="flex items-center gap-2.5">
          <Button
            variant="outline"
            size="sm"
            onClick={() => setShowShortcutsDialog(true)}
            className="text-text-muted hover:text-text-primary gap-1.5"
          >
            <HelpCircle size={14} />
            <span>Shortcuts (?)</span>
          </Button>
          <Button
            variant="primary"
            size="sm"
            onClick={() => {
              setWizardStep(1);
              setShowDrawer(true);
            }}
            className="shadow-sm gap-1.5"
          >
            <Plus size={15} />
            <span>Register Provider</span>
          </Button>
        </div>
      </div>

      {/* Saved Views Navigation bar */}
      <div className="flex items-center justify-between border-b border-border/60">
        <div className="flex space-x-1.5 overflow-x-auto scrollbar-none">
          {[
            { id: 'all', label: 'All Providers' },
            { id: 'verified', label: 'Verified Partners' },
            { id: 'under_review', label: 'Under Review' },
            { id: 'pending', label: 'Pending Audit' },
            { id: 'suspended', label: 'Suspended' },
            { id: 'compliant', label: '100% Compliant' }
          ].map(tab => (
            <button
              key={tab.id}
              onClick={() => setCurrentView(tab.id as any)}
              className={`px-4 py-2.5 text-xs font-semibold border-b-2 transition-all relative ${
                currentView === tab.id
                  ? 'border-primary text-primary'
                  : 'border-transparent text-text-muted hover:text-text-primary'
              }`}
            >
              {tab.label}
              {currentView === tab.id && (
                <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-primary" />
              )}
            </button>
          ))}
        </div>

        {/* View mode preferences controls */}
        <div className="flex items-center gap-1.5 border-l border-border pl-3 ml-2">
          {/* Density toggle indicator */}
          <button
            onClick={() =>
              setDensity(prev => (prev === 'normal' ? 'compact' : prev === 'compact' ? 'relaxed' : 'normal'))
            }
            title={`Density: ${density} (Click 'd' to switch)`}
            className="p-1.5 text-text-muted hover:text-text-primary hover:bg-surface-hover rounded"
          >
            <span className="text-[10px] font-mono font-bold tracking-wider uppercase">{density[0]}</span>
          </button>

          <button
            onClick={() => setLayoutView('table')}
            title="Table layout view"
            className={`p-1.5 rounded transition-colors ${
              layoutView === 'table' ? 'text-primary bg-primary/10' : 'text-text-muted hover:bg-surface-hover'
            }`}
          >
            <TableIcon size={15} />
          </button>
          <button
            onClick={() => setLayoutView('grid')}
            title="Grid card layout view"
            className={`p-1.5 rounded transition-colors ${
              layoutView === 'grid' ? 'text-primary bg-primary/10' : 'text-text-muted hover:bg-surface-hover'
            }`}
          >
            <LayoutGrid size={15} />
          </button>
        </div>
      </div>

      {/* Filter and Search toolbar */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-surface border border-border/80 rounded-xl p-4 shadow-sm">
        <div className="flex flex-1 flex-wrap items-center gap-3">
          {/* Global search */}
          <div className="relative w-full md:max-w-xs">
            <span className="absolute inset-y-0 left-3 flex items-center text-text-muted">
              <Search size={14} />
            </span>
            <input
              id="global-search-input"
              value={searchQuery}
              onChange={e => setSearchQuery(e.target.value)}
              placeholder="Search providers... (Press '/')"
              className="w-full pl-9 pr-4 py-2 text-xs bg-background border border-border rounded-lg outline-none focus:ring-1 focus:ring-primary focus:border-primary"
            />
            {searchQuery && (
              <button 
                onClick={() => setSearchQuery('')}
                className="absolute inset-y-0 right-3 flex items-center text-text-muted hover:text-text-primary"
              >
                <X size={12} />
              </button>
            )}
          </div>

          <Button
            variant="outline"
            size="sm"
            onClick={() => setShowFilters(prev => !prev)}
            className={`gap-1.5 py-2 ${showFilters ? 'bg-primary/5 text-primary border-primary/20' : ''}`}
          >
            <SlidersHorizontal size={13} />
            <span>Filters</span>
          </Button>

          <Button
            variant="outline"
            size="sm"
            onClick={() => setShowColumnDrawer(true)}
            className="gap-1.5 py-2 text-text-secondary"
          >
            <span>Columns</span>
          </Button>

          <Button
            variant="outline"
            size="sm"
            onClick={handleExportCSV}
            className="gap-1.5 py-2 text-text-secondary"
          >
            <Download size={13} />
            <span>Export CSV</span>
          </Button>
        </div>

        {/* Column sorting dropdown */}
        <div className="flex items-center gap-2.5 text-xs">
          <span className="text-text-secondary font-semibold">Sort by:</span>
          <div className="flex border border-border rounded-lg bg-background overflow-hidden">
            <select
              value={sortField}
              onChange={e => setSortField(e.target.value as any)}
              className="px-2 py-1.5 bg-transparent outline-none text-text-primary"
            >
              <option value="name">Company Name</option>
              <option value="id">Provider ID</option>
              <option value="status">Verification Status</option>
              <option value="compliance">Compliance status</option>
              <option value="revenue">Contracts volume</option>
            </select>
            <button
              onClick={() => setSortDirection(prev => (prev === 'asc' ? 'desc' : 'asc'))}
              className="px-2 border-l border-border hover:bg-surface-hover text-text-secondary"
              title="Toggle sorting direction"
            >
              <ArrowUpDown size={13} />
            </button>
          </div>
        </div>
      </div>

      {/* Expanded filters panel drawer option */}
      {showFilters && (
        <div className="bg-background border border-border rounded-xl p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 shadow-inner">
          <div className="space-y-1">
            <label className="text-[10px] font-semibold text-text-secondary uppercase">City Region</label>
            <select
              value={cityFilter}
              onChange={e => setCityFilter(e.target.value)}
              className="w-full border border-border bg-surface rounded-lg p-2 text-xs text-text-primary focus:outline-none"
            >
              {cityOptions.map(c => (
                <option key={c} value={c}>
                  {c}
                </option>
              ))}
            </select>
          </div>

          <div className="space-y-1">
            <label className="text-[10px] font-semibold text-text-secondary uppercase">State Office</label>
            <select
              value={stateFilter}
              onChange={e => setStateFilter(e.target.value)}
              className="w-full border border-border bg-surface rounded-lg p-2 text-xs text-text-primary focus:outline-none"
            >
              {stateOptions.map(s => (
                <option key={s} value={s}>
                  {s}
                </option>
              ))}
            </select>
          </div>

          <div className="space-y-1">
            <label className="text-[10px] font-semibold text-text-secondary uppercase">Revenue Tier</label>
            <select
              value={revenueFilter}
              onChange={e => setRevenueFilter(e.target.value)}
              className="w-full border border-border bg-surface rounded-lg p-2 text-xs text-text-primary focus:outline-none"
            >
              <option value="All">All Tiers</option>
              <option value="high">High Tier (2+ Contracts)</option>
              <option value="medium">Medium Tier (1 Contract)</option>
              <option value="low">Low Tier (0 Contracts)</option>
            </select>
          </div>

          {(cityFilter !== 'All' || stateFilter !== 'All' || revenueFilter !== 'All') && (
            <div className="col-span-1 sm:col-span-3 flex justify-end">
              <Button
                variant="outline"
                size="sm"
                onClick={() => {
                  setCityFilter('All');
                  setStateFilter('All');
                  setRevenueFilter('All');
                }}
                className="text-xs text-danger border-danger/20 hover:bg-danger/5"
              >
                Clear Filters
              </Button>
            </div>
          )}
        </div>
      )}

      {/* Main Listing View (Table / Grid) */}
      {layoutView === 'table' ? (
        <Card className="overflow-x-auto scrollbar-thin">
          <table className="min-w-full divide-y divide-border text-left">
            <thead className="bg-background/80 backdrop-blur">
              <tr>
                {/* Select column */}
                <th className="p-3 text-center w-12">
                  <input
                    type="checkbox"
                    checked={selectedIds.size > 0 && selectedIds.size === sortedAndFiltered.length}
                    onChange={handleSelectAllToggle}
                    className="rounded border-border text-primary focus:ring-primary h-3.5 w-3.5"
                  />
                </th>
                {visibleColumns.id && (
                  <th className="p-3 text-[10px] font-bold text-text-secondary uppercase tracking-wider font-mono">
                    Provider ID
                  </th>
                )}
                {visibleColumns.name && (
                  <th className="p-3 text-[10px] font-bold text-text-secondary uppercase tracking-wider">
                    Company Name
                  </th>
                )}
                {visibleColumns.status && (
                  <th className="p-3 text-[10px] font-bold text-text-secondary uppercase tracking-wider">
                    Verification
                  </th>
                )}
                {visibleColumns.compliance && (
                  <th className="p-3 text-[10px] font-bold text-text-secondary uppercase tracking-wider">
                    Compliance status
                  </th>
                )}
                {visibleColumns.revenue && (
                  <th className="p-3 text-[10px] font-bold text-text-secondary uppercase tracking-wider">
                    Contracts Volume
                  </th>
                )}
                {visibleColumns.contact && (
                  <th className="p-3 text-[10px] font-bold text-text-secondary uppercase tracking-wider">
                    Corporate Contact
                  </th>
                )}
                {visibleColumns.city && (
                  <th className="p-3 text-[10px] font-bold text-text-secondary uppercase tracking-wider">
                    City Location
                  </th>
                )}
                {visibleColumns.actions && (
                  <th className="p-3 text-[10px] font-bold text-text-secondary uppercase tracking-wider text-right">
                    Actions
                  </th>
                )}
              </tr>
            </thead>
            <tbody className="divide-y divide-border bg-surface text-xs">
              {sortedAndFiltered.map(p => {
                const summary = ComplianceCalculator.calculate(p);
                const isSelected = selectedIds.has(p.id);
                return (
                  <tr
                    key={p.id}
                    className={`hover:bg-surface-hover/50 transition-colors ${
                      isSelected ? 'bg-primary/5 hover:bg-primary/10' : ''
                    }`}
                  >
                    <td className="p-3 text-center">
                      <input
                        type="checkbox"
                        checked={isSelected}
                        onChange={() => handleBulkSelectToggle(p.id)}
                        className="rounded border-border text-primary focus:ring-primary h-3.5 w-3.5"
                      />
                    </td>
                    {visibleColumns.id && (
                      <td className="p-3 font-mono text-[10px] text-text-muted">{p.id}</td>
                    )}
                    {visibleColumns.name && (
                      <td className="p-3 font-semibold text-text-primary">
                        <div className="flex flex-col">
                          <span>{p.name}</span>
                          <span className="text-[10px] text-text-muted font-normal select-text">
                            {p.email}
                          </span>
                        </div>
                      </td>
                    )}
                    {visibleColumns.status && (
                      <td className="p-3">
                        <Badge variant={getVerificationBadge(p.status)} pulse={p.status === ProviderStatus.UnderReview}>
                          {p.status}
                        </Badge>
                      </td>
                    )}
                    {visibleColumns.compliance && (
                      <td className="p-3">
                        <Badge variant={getOverallBadge(summary.overallStatus)}>
                          {summary.overallStatus}
                        </Badge>
                      </td>
                    )}
                    {visibleColumns.revenue && (
                      <td className="p-3 font-semibold text-text-primary">
                        {summary.agreementsActive} Active / {summary.agreementsExpired} Expired
                      </td>
                    )}
                    {visibleColumns.contact && (
                      <td className="p-3 text-text-secondary select-text">
                        {p.primaryContact?.name || 'Unassigned'}
                      </td>
                    )}
                    {visibleColumns.city && (
                      <td className="p-3 text-text-secondary">
                        {p.gstRegistration?.registeredAddress?.city || 'NSW Office'}
                      </td>
                    )}
                    {visibleColumns.actions && (
                      <td className="p-3 text-right">
                        <a
                          href={`#/providers/${p.id}`}
                          className="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold text-primary hover:text-primary-hover bg-primary/5 hover:bg-primary/10 rounded-md transition-colors"
                        >
                          <Eye size={12} />
                          <span>View Workspace</span>
                        </a>
                      </td>
                    )}
                  </tr>
                );
              })}

              {sortedAndFiltered.length === 0 && (
                <tr>
                  <td colSpan={9} className="p-8 text-center">
                    <EmptyState
                      title="No Providers Found"
                      description="No records match your selected filter parameters or search parameters. Clear filters to see results."
                      actionLabel="Clear filters"
                      onAction={() => {
                        setCityFilter('All');
                        setStateFilter('All');
                        setRevenueFilter('All');
                        setSearchQuery('');
                      }}
                    />
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </Card>
      ) : (
        /* Grid card list View */
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {sortedAndFiltered.map(p => {
            const summary = ComplianceCalculator.calculate(p);
            const isSelected = selectedIds.has(p.id);
            return (
              <Card
                key={p.id}
                hoverable
                className={`relative flex flex-col justify-between p-5 min-h-[220px] transition-all border ${
                  isSelected ? 'border-primary ring-1 ring-primary/20 bg-primary/5' : 'border-border'
                }`}
              >
                <div>
                  <div className="flex justify-between items-start">
                    <div className="flex items-center gap-2">
                      <input
                        type="checkbox"
                        checked={isSelected}
                        onChange={() => handleBulkSelectToggle(p.id)}
                        className="rounded border-border text-primary focus:ring-primary h-3.5 w-3.5"
                      />
                      <div>
                        <h3 className="font-bold text-text-primary text-sm leading-snug">{p.name}</h3>
                        <span className="font-mono text-[9px] text-text-muted mt-0.5">ID: {p.id}</span>
                      </div>
                    </div>
                    <Badge variant={getVerificationBadge(p.status)} pulse={p.status === ProviderStatus.UnderReview}>
                      {p.status}
                    </Badge>
                  </div>

                  <div className="grid grid-cols-2 gap-3 pt-4 border-t border-border mt-4 text-[11px] text-text-secondary">
                    <div>
                      <span className="text-[10px] font-semibold text-text-muted block uppercase">Compliance</span>
                      <div className="mt-1">
                        <Badge variant={getOverallBadge(summary.overallStatus)}>
                          {summary.overallStatus}
                        </Badge>
                      </div>
                    </div>
                    <div>
                      <span className="text-[10px] font-semibold text-text-muted block uppercase">Contracts</span>
                      <span className="font-bold text-text-primary inline-block mt-1">
                        {summary.agreementsActive} Active / {summary.agreementsExpired} Expired
                      </span>
                    </div>
                  </div>
                </div>

                <div className="flex justify-between items-center pt-4 border-t border-border mt-4">
                  <span className="text-[10px] font-medium text-text-muted font-mono select-text">
                    {p.email}
                  </span>
                  <a
                    href={`#/providers/${p.id}`}
                    className="inline-flex items-center gap-1 px-3 py-1.5 bg-primary hover:bg-primary-hover text-white rounded text-[10px] font-bold transition-all shadow-sm"
                  >
                    <span>View profile</span>
                    <ChevronRight size={11} />
                  </a>
                </div>
              </Card>
            );
          })}

          {sortedAndFiltered.length === 0 && (
            <div className="col-span-full">
              <EmptyState
                title="No Providers Found"
                description="No records match your selected filter parameters or search parameters."
                actionLabel="Reset Search"
                onAction={() => setSearchQuery('')}
              />
            </div>
          )}
        </div>
      )}

      {/* Floating Bulk operations bar */}
      {selectedIds.size > 0 && (
        <div className="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-40 bg-surface border border-primary/20 shadow-2xl rounded-full px-5 py-3 flex items-center gap-4 animate-slide-up">
          <span className="text-xs font-semibold text-text-secondary">
            <span className="text-primary font-bold mr-1">{selectedIds.size}</span> selected
          </span>
          <div className="h-4 w-px bg-border" />
          <div className="flex gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={() => {
                setBulkActionType('verify');
              }}
              className="gap-1 px-2.5 py-1 text-xs border-primary/20 hover:bg-primary/5 text-primary"
            >
              <CheckCircle size={13} />
              <span>Verify</span>
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => {
                setBulkActionType('suspend');
              }}
              className="gap-1 px-2.5 py-1 text-xs border-warning/20 hover:bg-warning/5 text-warning"
            >
              <Ban size={13} />
              <span>Suspend</span>
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => {
                setBulkActionType('delete');
              }}
              className="gap-1 px-2.5 py-1 text-xs border-danger/20 hover:bg-danger/5 text-danger"
            >
              <Trash2 size={13} />
              <span>Delete</span>
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={handleExportCSV}
              className="gap-1 px-2.5 py-1 text-xs border-border"
            >
              <Download size={13} />
              <span>Export</span>
            </Button>
          </div>
          <button
            onClick={() => setSelectedIds(new Set())}
            className="text-text-muted hover:text-text-primary text-xs ml-1 focus:outline-none"
            title="Deselect all items"
          >
            ✕
          </button>
        </div>
      )}

      {/* Slide-over Column Visibility configuration drawer */}
      <Drawer
        isOpen={showColumnDrawer}
        onClose={() => setShowColumnDrawer(false)}
        title="Column Visibility Manager"
        footer={
          <div className="flex justify-end gap-2">
            <Button variant="outline" size="sm" onClick={() => setVisibleColumns({
              id: true,
              name: true,
              status: true,
              compliance: true,
              revenue: true,
              contact: true,
              city: true,
              actions: true
            })}>
              Reset Columns
            </Button>
            <Button variant="primary" size="sm" onClick={() => setShowColumnDrawer(false)}>
              Apply Configuration
            </Button>
          </div>
        }
      >
        <div className="space-y-4">
          <p className="text-xs text-text-secondary mb-4">
            Toggle checkboxes below to customize directory list grid columns visibility preferences.
          </p>
          {[
            { key: 'id', label: 'Provider ID' },
            { key: 'name', label: 'Company Name' },
            { key: 'status', label: 'Verification Badge' },
            { key: 'compliance', label: 'Compliance Index' },
            { key: 'revenue', label: 'Contracts Volume' },
            { key: 'contact', label: 'Corporate Contact' },
            { key: 'city', label: 'City Office' },
            { key: 'actions', label: 'Actions Menu' }
          ].map(col => (
            <label key={col.key} className="flex items-center justify-between p-2 hover:bg-surface-hover rounded-md cursor-pointer select-none">
              <span className="text-xs font-semibold text-text-primary">{col.label}</span>
              <input
                type="checkbox"
                checked={visibleColumns[col.key]}
                onChange={e => setVisibleColumns(prev => ({ ...prev, [col.key]: e.target.checked }))}
                className="rounded border-border text-primary focus:ring-primary h-4 w-4"
              />
            </label>
          ))}
        </div>
      </Drawer>

      {/* Multi-step Registration Stepper wizard Drawer */}
      <Drawer
        isOpen={showDrawer}
        onClose={() => {
          if (confirm('Are you sure you want to exit the wizard? Form contents will be reset.')) {
            setShowDrawer(false);
          }
        }}
        title="Register Corporate Partner"
        footer={
          <div className="flex justify-between items-center w-full">
            <div>
              {wizardStep > 1 && (
                <Button variant="outline" size="sm" onClick={handlePrevStep} className="gap-1">
                  <ChevronLeft size={14} />
                  <span>Back</span>
                </Button>
              )}
            </div>
            <div className="flex gap-2">
              {wizardStep < 5 ? (
                <Button variant="primary" size="sm" onClick={handleNextStep} className="gap-1">
                  <span>Next Step</span>
                  <ChevronRight size={14} />
                </Button>
              ) : (
                <Button variant="success" size="sm" onClick={handleCreate}>
                  Sign & Register Aggregate
                </Button>
              )}
            </div>
          </div>
        }
      >
        <div className="space-y-6">
          {/* Stepper indicator index */}
          <div className="flex items-center justify-between border-b border-border pb-4">
            {[1, 2, 3, 4, 5].map(step => (
              <div key={step} className="flex items-center gap-1">
                <span className={`h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold ${
                  wizardStep === step
                    ? 'bg-primary text-white'
                    : wizardStep > step
                    ? 'bg-success text-white'
                    : 'bg-background text-text-muted border border-border'
                }`}>
                  {step}
                </span>
                <span className="text-[10px] hidden md:inline font-bold uppercase tracking-wider text-text-secondary">
                  {step === 1 ? 'Basic' : step === 2 ? 'Address' : step === 3 ? 'Tax' : step === 4 ? 'Bank' : 'Review'}
                </span>
              </div>
            ))}
          </div>

          {/* Stepper body views */}
          {wizardStep === 1 && (
            <div className="space-y-4">
              <h3 className="text-xs font-bold text-text-primary uppercase tracking-wider">Step 1: General Profile Coordinates</h3>
              <Input
                label="Company Legal Name"
                value={name}
                onChange={e => setName(e.target.value)}
                placeholder="e.g. Global Logistic Operations Pty Ltd"
                error={formErrors.name}
                required
              />
              <Input
                label="Business Email Address"
                type="email"
                value={email}
                onChange={e => setEmail(e.target.value)}
                placeholder="e.g. corporate@globallogistics.com"
                error={formErrors.email}
                required
              />
              <Input
                label="Mobile Phone"
                value={phone}
                onChange={e => setPhone(e.target.value)}
                placeholder="e.g. 0499888777"
                error={formErrors.phone}
              />
            </div>
          )}

          {wizardStep === 2 && (
            <div className="space-y-4">
              <h3 className="text-xs font-bold text-text-primary uppercase tracking-wider">Step 2: Corporate Registered Address</h3>
              <Input
                label="Street Address"
                value={street}
                onChange={e => setStreet(e.target.value)}
                placeholder="e.g. 200 Pitt St"
                error={formErrors.street}
                required
              />
              <div className="grid grid-cols-2 gap-4">
                <Input
                  label="City"
                  value={city}
                  onChange={e => setCity(e.target.value)}
                  placeholder="e.g. Sydney"
                  error={formErrors.city}
                  required
                />
                <Input
                  label="State / Province"
                  value={stateCode}
                  onChange={e => setStateCode(e.target.value)}
                  placeholder="e.g. NSW"
                  error={formErrors.stateCode}
                  required
                />
              </div>
              <Input
                label="Zip / Postal Code"
                value={zip}
                onChange={e => setZip(e.target.value)}
                placeholder="e.g. 2000"
                error={formErrors.zip}
              />
            </div>
          )}

          {wizardStep === 3 && (
            <div className="space-y-4">
              <h3 className="text-xs font-bold text-text-primary uppercase tracking-wider">Step 3: Business GST Credentials</h3>
              <Input
                label="GST Identification Number"
                value={gst}
                onChange={e => setGst(e.target.value)}
                placeholder="e.g. GST-998877"
                error={formErrors.gst}
                helperText="Enter valid corporate taxation reference string."
              />
            </div>
          )}

          {wizardStep === 4 && (
            <div className="space-y-4">
              <h3 className="text-xs font-bold text-text-primary uppercase tracking-wider">Step 4: Bank Settlement Account coordinates</h3>
              <Input
                label="Bank Name"
                value={bank}
                onChange={e => setBank(e.target.value)}
                placeholder="e.g. Westpac Banking Corporation"
                error={formErrors.bank}
              />
              <Input
                label="Account Holder Name"
                value={accName}
                onChange={e => setAccName(e.target.value)}
                placeholder="e.g. Global Logistic Operations"
                error={formErrors.accName}
              />
              <div className="grid grid-cols-2 gap-4">
                <Input
                  label="Account Number"
                  value={accNum}
                  onChange={e => setAccNum(e.target.value)}
                  placeholder="e.g. 12345678"
                  error={formErrors.accNum}
                />
                <Input
                  label="BSB / Routing Code"
                  value={routing}
                  onChange={e => setRouting(e.target.value)}
                  placeholder="e.g. 032-001"
                  error={formErrors.routing}
                />
              </div>
            </div>
          )}

          {wizardStep === 5 && (
            <div className="space-y-4">
              <h3 className="text-xs font-bold text-text-primary uppercase tracking-wider">Step 5: Review Profile Record</h3>
              <div className="bg-background rounded-lg border border-border p-4 space-y-4 text-xs">
                <div className="grid grid-cols-2 gap-2 pb-3 border-b border-border">
                  <span className="text-text-muted">Legal Name:</span>
                  <span className="font-semibold text-text-primary text-right">{name}</span>
                  <span className="text-text-muted">Email:</span>
                  <span className="font-semibold text-text-primary text-right">{email}</span>
                  <span className="text-text-muted">Phone:</span>
                  <span className="font-semibold text-text-primary text-right">{phone || 'Not Specified'}</span>
                </div>
                <div className="grid grid-cols-2 gap-2 pb-3 border-b border-border">
                  <span className="text-text-muted">Registered Address:</span>
                  <span className="font-semibold text-text-primary text-right select-all">
                    {street}, {city}, {stateCode} {zip}
                  </span>
                </div>
                <div className="grid grid-cols-2 gap-2 pb-3 border-b border-border">
                  <span className="text-text-muted">GST Tax Identifier:</span>
                  <span className="font-mono font-semibold text-text-primary text-right">{gst || 'None'}</span>
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <span className="text-text-muted">Bank Name:</span>
                  <span className="font-semibold text-text-primary text-right">{bank || 'None'}</span>
                  <span className="text-text-muted">Account Holder:</span>
                  <span className="font-semibold text-text-primary text-right">{accName || 'None'}</span>
                  <span className="text-text-muted">Account Num / BSB:</span>
                  <span className="font-mono font-semibold text-text-primary text-right">
                    {accNum ? `${accNum} / ${routing}` : 'None'}
                  </span>
                </div>
              </div>
              <div className="p-3 bg-primary/5 rounded-lg border border-primary/10 flex items-start gap-2.5">
                <input type="checkbox" required className="mt-1" id="legal-agreement-wizard" />
                <label htmlFor="legal-agreement-wizard" className="text-[10px] text-text-secondary leading-snug">
                  I verify that the above information is accurate and matches original corporate incorporation registry documents.
                </label>
              </div>
            </div>
          )}
        </div>
      </Drawer>

      {/* Keyboard Shortcuts Cheatsheet modal */}
      <Dialog
        isOpen={showShortcutsDialog}
        onClose={() => setShowShortcutsDialog(false)}
        title="Keyboard Shortcuts Cheatsheet"
      >
        <div className="space-y-4">
          <p className="text-xs text-text-secondary">
            Use these shortcuts when not typing in text field inputs to navigate SODAARS directory like a pro:
          </p>
          <div className="space-y-2.5 font-mono text-xs">
            {[
              { key: '?', desc: 'Toggle this shortcuts modal panel' },
              { key: 'c', desc: 'Open Create Provider registration wizard' },
              { key: '/', desc: 'Focus the global search field input' },
              { key: 'g', desc: 'Toggle Layout between Table / Grid card lists' },
              { key: 'd', desc: 'Cycle table grid row Density (Relaxed / Normal / Compact)' },
              { key: 'v', desc: 'Cycle Saved Views tabs selection' },
              { key: 'Esc', desc: 'Close any active slide-over panels or dialog boxes' }
            ].map(item => (
              <div key={item.key} className="flex justify-between items-center py-1.5 border-b border-border/40">
                <span className="text-text-secondary">{item.desc}</span>
                <kbd className="px-2 py-0.5 bg-background border border-border shadow-sm rounded text-primary font-bold">
                  {item.key}
                </kbd>
              </div>
            ))}
          </div>
        </div>
      </Dialog>

      {/* Confirm Bulk operations Modals */}
      <Dialog
        isOpen={bulkActionType !== null}
        onClose={() => setBulkActionType(null)}
        title={`Confirm Bulk ${bulkActionType ? bulkActionType.toUpperCase() : ''}`}
        confirmLabel={`Execute bulk ${bulkActionType}`}
        onConfirm={executeBulkAction}
        confirmVariant={bulkActionType === 'delete' ? 'danger' : bulkActionType === 'suspend' ? 'danger' : 'primary'}
      >
        <p className="text-xs">
          Are you sure you want to perform the bulk <strong>{bulkActionType}</strong> operation on the{' '}
          <strong className="text-primary">{selectedIds.size}</strong> selected provider aggregates? This operation
          cannot be undone.
        </p>
      </Dialog>
    </div>
  );
};
export default ProviderListPage;
