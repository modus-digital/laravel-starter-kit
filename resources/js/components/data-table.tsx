import {
    type ColumnDef,
    type ColumnFiltersState,
    type RowSelectionState,
    type SortingState,
    type VisibilityState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getSortedRowModel,
    useReactTable,
} from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, Settings2 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';

type DataTableProps<TData, TValue> = {
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    /**
     * One or more column ids (accessorKey / id) to apply the search input to.
     * The same search text will be applied to all of these columns.
     */
    searchColumnIds?: string[];
    searchPlaceholder?: string;
    onRowClick?: (row: TData) => void;
    enableRowSelection?: boolean;
    onSelectionChange?: (rows: TData[]) => void;
    bulkActionsRender?: (selectedRows: TData[]) => React.ReactNode;
    pagination?: {
        currentPage: number;
        lastPage: number;
        total?: number;
        onPageChange: (page: number) => void;
    };
};

export function DataTable<TData, TValue>({
    columns,
    data,
    searchColumnIds,
    searchPlaceholder = 'Search…',
    onRowClick,
    enableRowSelection,
    onSelectionChange,
    bulkActionsRender,
    pagination,
}: DataTableProps<TData, TValue>) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = useState<RowSelectionState>({});

    const table = useReactTable({
        data,
        columns,
        state: {
            sorting,
            columnFilters,
            columnVisibility,
            rowSelection,
        },
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        onColumnVisibilityChange: setColumnVisibility,
        onRowSelectionChange: setRowSelection,
        enableRowSelection: enableRowSelection ?? true,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
    });

    const selectedRows = useMemo(() => table.getSelectedRowModel().rows.map((row) => row.original), [rowSelection, table]);

    useEffect(() => {
        if (!enableRowSelection || !onSelectionChange) {
            return;
        }

        onSelectionChange(selectedRows);
    }, [enableRowSelection, onSelectionChange, rowSelection, selectedRows]);

    const searchValue =
        searchColumnIds && searchColumnIds.length > 0 ? ((table.getColumn(searchColumnIds[0])?.getFilterValue() as string) ?? '') : '';

    const handleSearchChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        if (!searchColumnIds?.length) {
            return;
        }

        const value = event.target.value;

        searchColumnIds.forEach((columnId) => {
            const column = table.getColumn(columnId);

            if (column) {
                column.setFilterValue(value);
            }
        });
    };

    return (
        <div className="space-y-3">
            {searchColumnIds && searchColumnIds.length > 0 && (
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Input placeholder={searchPlaceholder} value={searchValue} onChange={handleSearchChange} className="h-8 max-w-xs" />
                    <div className="mt-1 flex items-center justify-between gap-2 text-xs text-muted-foreground sm:mt-0 sm:ml-auto">
                        <span>
                            {table.getFilteredRowModel().rows.length} of {pagination?.total ?? data.length} row
                            {table.getFilteredRowModel().rows.length === 1 ? '' : 's'}
                        </span>
                        <div className="flex items-center gap-2">
                            {enableRowSelection && bulkActionsRender && (
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="hidden h-8 gap-1 px-2 sm:inline-flex"
                                            disabled={selectedRows.length === 0}
                                        >
                                            <span>Bulk</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" className="w-[200px]">
                                        {bulkActionsRender(selectedRows)}
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            )}
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="outline" size="sm" className="hidden h-8 gap-1 px-2 sm:inline-flex">
                                        <Settings2 className="size-3.5" />
                                        <span>View</span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" className="w-[180px]">
                                    <DropdownMenuLabel>Toggle columns</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    {table
                                        .getAllColumns()
                                        .filter((column) => typeof column.accessorFn !== 'undefined' && column.getCanHide())
                                        .map((column) => (
                                            <DropdownMenuCheckboxItem
                                                key={column.id}
                                                className="capitalize"
                                                checked={column.getIsVisible()}
                                                onCheckedChange={(value) => column.toggleVisibility(!!value)}
                                            >
                                                {column.id}
                                            </DropdownMenuCheckboxItem>
                                        ))}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>
            )}

            <div className="overflow-hidden rounded-lg border border-border/70 bg-card">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => (
                                    <TableHead key={header.id}>
                                        {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                    </TableHead>
                                ))}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow
                                    key={row.id}
                                    data-state={row.getIsSelected() && 'selected'}
                                    className={cn(onRowClick ? 'cursor-pointer' : undefined)}
                                    onClick={
                                        onRowClick
                                            ? (event) => {
                                                  // Allow inner elements to stop propagation.
                                                  if ((event.target as HTMLElement).closest('[data-row-action],[data-row-select]')) {
                                                      return;
                                                  }

                                                  onRowClick(row.original);
                                              }
                                            : undefined
                                    }
                                    onKeyDown={
                                        onRowClick
                                            ? (event) => {
                                                  if (event.key === 'Enter' || event.key === ' ') {
                                                      event.preventDefault();
                                                      onRowClick(row.original);
                                                  }
                                              }
                                            : undefined
                                    }
                                    tabIndex={onRowClick ? 0 : -1}
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={table.getAllColumns().length} className="h-24 text-center text-sm text-muted-foreground">
                                    No results found.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
                {pagination && pagination.lastPage > 1 && (
                    <div className="flex flex-col gap-2 border-t border-border/70 px-4 py-2 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between sm:text-sm">
                        <span>
                            Page {pagination.currentPage} of {pagination.lastPage}
                        </span>
                        <div className="flex items-center justify-end gap-2">
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-7 w-7 sm:h-8 sm:w-8"
                                onClick={() => pagination.onPageChange(pagination.currentPage - 1)}
                                disabled={pagination.currentPage <= 1}
                            >
                                <span className="sr-only">Previous page</span>
                                <ChevronLeft className="size-4" />
                            </Button>
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-7 w-7 sm:h-8 sm:w-8"
                                onClick={() => pagination.onPageChange(pagination.currentPage + 1)}
                                disabled={pagination.currentPage >= pagination.lastPage}
                            >
                                <span className="sr-only">Next page</span>
                                <ChevronRight className="size-4" />
                            </Button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
