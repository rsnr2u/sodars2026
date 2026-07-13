import React, { useState, useMemo } from 'react';
import { ActionIcons, NavigationIcons, StatusIcons } from '@sodars/icons';

export interface ColumnDef<T> {
  id: string;
  header: string;
  accessor: (row: T) => React.ReactNode;
  sortValue?: (row: T) => string | number;
  searchValue?: (row: T) => string;
}

export interface EnterpriseDataGridProps<T> {
  data: T[];
  columns: ColumnDef<T>[];
  searchPlaceholder?: string;
  onRowClick?: (row: T) => void;
  bulkActions?: Array<{
    label: string;
    icon?: React.ComponentType<any>;
    onClick: (selected: T[]) => void;
    variant?: 'primary' | 'danger' | 'secondary';
  }>;
  isLoading?: boolean;
  isError?: boolean;
  errorMessage?: string;
}

export function EnterpriseDataGrid<T extends { id: string }>({
  data,
  columns,
  searchPlaceholder = 'Search records...',
  onRowClick,
  bulkActions = [],
  isLoading = false,
  isError = false,
  errorMessage = 'Failed to load records'
}: EnterpriseDataGridProps<T>) {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set());
  const [sortColumn, setSortColumn] = useState<string | null>(null);
  const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);
  const [density, setDensity] = useState<'compact' | 'normal' | 'relaxed'>('normal');
  const [visibleColumns, setVisibleColumns] = useState<Set<string>>(
    new Set(columns.map(c => c.id))
  );
  const [showColumnDropdown, setShowColumnDropdown] = useState(false);

  // Filter Data
  const filteredData = useMemo(() => {
    if (!searchQuery) return data;
    const q = searchQuery.toLowerCase();
    return data.filter(row => {
      return columns.some(col => {
        if (!col.searchValue) return false;
        return col.searchValue(row).toLowerCase().includes(q);
      });
    });
  }, [data, columns, searchQuery]);

  // Sort Data
  const sortedData = useMemo(() => {
    if (!sortColumn) return filteredData;
    const col = columns.find(c => c.id === sortColumn);
    if (!col || !col.sortValue) return filteredData;

    const sorted = [...filteredData].sort((a, b) => {
      const aVal = col.sortValue!(a);
      const bVal = col.sortValue!(b);
      if (typeof aVal === 'number' && typeof bVal === 'number') {
        return sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
      }
      return sortDirection === 'asc'
        ? String(aVal).localeCompare(String(bVal))
        : String(bVal).localeCompare(String(aVal));
    });
    return sorted;
  }, [filteredData, columns, sortColumn, sortDirection]);

  // Paginated Data
  const paginatedData = useMemo(() => {
    const start = (currentPage - 1) * pageSize;
    return sortedData.slice(start, start + pageSize);
  }, [sortedData, currentPage, pageSize]);

  const totalPages = Math.ceil(sortedData.length / pageSize);

  // Selection handlers
  const handleSelectAll = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.checked) {
      const newSelection = new Set<string>();
      paginatedData.forEach(row => newSelection.add(row.id));
      setSelectedIds(newSelection);
    } else {
      setSelectedIds(new Set());
    }
  };

  const handleSelectRow = (id: string) => {
    const newSelection = new Set(selectedIds);
    if (newSelection.has(id)) {
      newSelection.delete(id);
    } else {
      newSelection.add(id);
    }
    setSelectedIds(newSelection);
  };

  const handleSort = (columnId: string) => {
    const col = columns.find(c => c.id === columnId);
    if (!col || !col.sortValue) return;

    if (sortColumn === columnId) {
      if (sortDirection === 'asc') {
        setSortDirection('desc');
      } else {
        setSortColumn(null);
      }
    } else {
      setSortColumn(columnId);
      setSortDirection('asc');
    }
  };

  const selectedRows = useMemo(() => {
    return data.filter(row => selectedIds.has(row.id));
  }, [data, selectedIds]);

  const densityPadding = {
    compact: 'py-2 px-3 text-xs',
    normal: 'py-3.5 px-4 text-sm',
    relaxed: 'py-5 px-6 text-base'
  };

  return (
    <div className="flex flex-col h-full bg-surface border border-border rounded-lg shadow-sm overflow-hidden">
      {/* Table Toolbar */}
      <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between p-4 gap-3 border-b border-border bg-surface">
        {/* Search */}
        <div className="relative flex-1 max-w-md">
          <ActionIcons.Search className="absolute left-3 top-2.5 h-4 w-4 text-text-muted" />
          <input
            type="text"
            placeholder={searchPlaceholder}
            value={searchQuery}
            onChange={e => {
              setSearchQuery(e.target.value);
              setCurrentPage(1);
            }}
            className="w-full pl-9 pr-4 py-2 border border-border rounded-md text-sm bg-background text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
          />
        </div>

        {/* View Controls */}
        <div className="flex items-center gap-2 self-end sm:self-auto">
          {/* Density Selector */}
          <div className="flex items-center border border-border rounded-md p-0.5 bg-background">
            <button
              onClick={() => setDensity('compact')}
              className={`px-2.5 py-1 text-xs font-medium rounded-sm transition-colors ${
                density === 'compact' ? 'bg-surface text-primary shadow-sm' : 'text-text-muted hover:text-text-primary'
              }`}
            >
              Compact
            </button>
            <button
              onClick={() => setDensity('normal')}
              className={`px-2.5 py-1 text-xs font-medium rounded-sm transition-colors ${
                density === 'normal' ? 'bg-surface text-primary shadow-sm' : 'text-text-muted hover:text-text-primary'
              }`}
            >
              Normal
            </button>
            <button
              onClick={() => setDensity('relaxed')}
              className={`px-2.5 py-1 text-xs font-medium rounded-sm transition-colors ${
                density === 'relaxed' ? 'bg-surface text-primary shadow-sm' : 'text-text-muted hover:text-text-primary'
              }`}
            >
              Relaxed
            </button>
          </div>

          {/* Column Toggle Dropdown */}
          <div className="relative">
            <button
              onClick={() => setShowColumnDropdown(!showColumnDropdown)}
              className="flex items-center gap-1.5 px-3 py-2 border border-border rounded-md text-sm text-text-secondary hover:bg-surface-hover hover:text-text-primary transition-colors bg-surface"
            >
              <ActionIcons.Filter className="h-4 w-4" />
              Columns
            </button>

            {showColumnDropdown && (
              <div className="absolute right-0 mt-1.5 w-48 border border-border bg-surface rounded-md shadow-lg py-1.5 z-50">
                <div className="px-3 py-1 text-xs font-semibold text-text-muted border-b border-border mb-1">
                  Toggle Columns
                </div>
                {columns.map(col => (
                  <label
                    key={col.id}
                    className="flex items-center gap-2 px-3 py-1.5 hover:bg-surface-hover cursor-pointer text-sm text-text-secondary"
                  >
                    <input
                      type="checkbox"
                      checked={visibleColumns.has(col.id)}
                      onChange={() => {
                        const newVisible = new Set(visibleColumns);
                        if (newVisible.has(col.id)) {
                          if (newVisible.size > 1) newVisible.delete(col.id);
                        } else {
                          newVisible.add(col.id);
                        }
                        setVisibleColumns(newVisible);
                      }}
                      className="rounded border-border text-primary focus:ring-primary/20"
                    />
                    {col.header}
                  </label>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Bulk Action Bar (Floating notification overlay style) */}
      {selectedRows.length > 0 && bulkActions.length > 0 && (
        <div className="flex items-center justify-between px-4 py-3 bg-primary-light border-b border-border animate-fade-in">
          <span className="text-sm text-primary font-medium">
            {selectedRows.length} {selectedRows.length === 1 ? 'row' : 'rows'} selected
          </span>
          <div className="flex items-center gap-2">
            {bulkActions.map((action, idx) => (
              <button
                key={idx}
                onClick={() => action.onClick(selectedRows)}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold shadow-sm transition-colors cursor-pointer ${
                  action.variant === 'danger'
                    ? 'bg-danger text-white hover:bg-danger/90'
                    : 'bg-primary text-white hover:bg-primary-hover'
                }`}
              >
                {action.label}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Main Grid Viewport */}
      <div className="flex-1 overflow-auto min-h-[400px]">
        {isLoading ? (
          /* Loading State: Premium Shimmer Skeletons */
          <div className="flex flex-col p-4 gap-3 animate-pulse">
            <div className="h-8 bg-border rounded w-1/4 mb-4"></div>
            {Array.from({ length: 8 }).map((_, idx) => (
              <div key={idx} className="flex gap-4 items-center border-b border-border py-3">
                <div className="h-4 bg-border rounded w-4"></div>
                <div className="h-4 bg-border rounded flex-1"></div>
                <div className="h-4 bg-border rounded w-24"></div>
                <div className="h-4 bg-border rounded w-32"></div>
                <div className="h-4 bg-border rounded w-16"></div>
              </div>
            ))}
          </div>
        ) : isError ? (
          /* Error State */
          <div className="flex flex-col items-center justify-center p-12 text-center h-full">
            <StatusIcons.Error className="h-12 w-12 text-danger mb-3" />
            <h3 className="text-lg font-semibold text-text-primary mb-1">Error loading records</h3>
            <p className="text-sm text-text-muted mb-4">{errorMessage}</p>
          </div>
        ) : filteredData.length === 0 ? (
          /* Empty State / No Search Results */
          <div className="flex flex-col items-center justify-center p-12 text-center h-full">
            <ActionIcons.Ban className="h-12 w-12 text-text-muted mb-3" />
            <h3 className="text-lg font-semibold text-text-primary mb-1">No records found</h3>
            <p className="text-sm text-text-muted">Try adjusting your search query or filters.</p>
          </div>
        ) : (
          <table className="w-full text-left border-collapse relative">
            {/* Sticky Table Header */}
            <thead className="sticky top-0 bg-surface z-10 border-b border-border">
              <tr>
                <th className="py-3 px-4 w-12">
                  <input
                    type="checkbox"
                    checked={paginatedData.every(row => selectedIds.has(row.id))}
                    onChange={handleSelectAll}
                    className="rounded border-border text-primary focus:ring-primary/20 cursor-pointer"
                  />
                </th>
                {columns
                  .filter(col => visibleColumns.has(col.id))
                  .map(col => (
                    <th
                      key={col.id}
                      onClick={() => handleSort(col.id)}
                      className={`py-3 px-4 text-xs font-semibold uppercase tracking-wider text-text-muted border-b border-border ${
                        col.sortValue ? 'cursor-pointer select-none hover:text-text-primary' : ''
                      }`}
                    >
                      <div className="flex items-center gap-1.5">
                        {col.header}
                        {sortColumn === col.id ? (
                          sortDirection === 'asc' ? (
                            <NavigationIcons.ChevronUp className="h-3 w-3" />
                          ) : (
                            <NavigationIcons.ChevronDown className="h-3 w-3" />
                          )
                        ) : col.sortValue ? (
                          <ActionIcons.ArrowUpDown className="h-3 w-3 text-text-muted/40" />
                        ) : null}
                      </div>
                    </th>
                  ))}
              </tr>
            </thead>
            {/* Table Body */}
            <tbody>
              {paginatedData.map(row => (
                <tr
                  key={row.id}
                  onClick={() => onRowClick?.(row)}
                  className={`border-b border-border transition-colors hover:bg-surface-hover ${
                    onRowClick ? 'cursor-pointer' : ''
                  } ${selectedIds.has(row.id) ? 'bg-primary-light/30' : ''}`}
                >
                  <td className="py-2.5 px-4" onClick={e => e.stopPropagation()}>
                    <input
                      type="checkbox"
                      checked={selectedIds.has(row.id)}
                      onChange={() => handleSelectRow(row.id)}
                      className="rounded border-border text-primary focus:ring-primary/20 cursor-pointer"
                    />
                  </td>
                  {columns
                    .filter(col => visibleColumns.has(col.id))
                    .map(col => (
                      <td key={col.id} className={densityPadding[density]}>
                        {col.accessor(row)}
                      </td>
                    ))}
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* Pagination Footer */}
      {!isLoading && !isError && filteredData.length > 0 && (
        <div className="flex flex-col sm:flex-row items-center justify-between p-4 border-t border-border bg-surface gap-3 text-sm text-text-secondary">
          <div className="flex items-center gap-4">
            <span>
              Showing {(currentPage - 1) * pageSize + 1} to{' '}
              {Math.min(currentPage * pageSize, filteredData.length)} of {filteredData.length} entries
            </span>
            <div className="flex items-center gap-1.5">
              <span>Rows per page:</span>
              <select
                value={pageSize}
                onChange={e => {
                  setPageSize(Number(e.target.value));
                  setCurrentPage(1);
                }}
                className="border border-border rounded px-2 py-1 bg-surface text-text-primary text-sm focus:outline-none focus:ring-1 focus:ring-primary"
              >
                {[5, 10, 20, 50].map(val => (
                  <option key={val} value={val}>
                    {val}
                  </option>
                ))}
              </select>
            </div>
          </div>

          <div className="flex items-center gap-1">
            <button
              onClick={() => setCurrentPage(1)}
              disabled={currentPage === 1}
              className="p-1.5 border border-border rounded hover:bg-surface-hover hover:text-text-primary disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-text-secondary cursor-pointer transition-colors"
            >
              First
            </button>
            <button
              onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
              disabled={currentPage === 1}
              className="p-1.5 border border-border rounded hover:bg-surface-hover hover:text-text-primary disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-text-secondary cursor-pointer transition-colors"
            >
              Previous
            </button>
            <span className="px-3 py-1 bg-background border border-border rounded font-medium text-text-primary text-sm">
              Page {currentPage} of {totalPages}
            </span>
            <button
              onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
              disabled={currentPage === totalPages}
              className="p-1.5 border border-border rounded hover:bg-surface-hover hover:text-text-primary disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-text-secondary cursor-pointer transition-colors"
            >
              Next
            </button>
            <button
              onClick={() => setCurrentPage(totalPages)}
              disabled={currentPage === totalPages}
              className="p-1.5 border border-border rounded hover:bg-surface-hover hover:text-text-primary disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-text-secondary cursor-pointer transition-colors"
            >
              Last
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
