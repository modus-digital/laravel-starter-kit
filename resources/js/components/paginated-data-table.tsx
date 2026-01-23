import {
    type ColumnDef,
    type ColumnFiltersState,
    type PaginationState,
    type RowSelectionState,
    type SortingState,
    type VisibilityState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useReactTable,
} from '@tanstack/react-table';
import { ChevronDownIcon, ChevronFirstIcon, ChevronLastIcon, ChevronLeftIcon, ChevronRightIcon, ChevronUpIcon, Settings2 } from 'lucide-react';
import { useEffect, useId, useMemo, useState } from 'react';

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
import { Label } from '@/components/ui/label';
import { Pagination, PaginationContent, PaginationItem } from '@/components/ui/pagination';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

import { cn } from '@/lib/utils';

type PaginatedDataTableProps<TData, TValue> = {
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
    defaultPageSize?: number;
    pageSizeOptions?: number[];
};

export function PaginatedDataTable<TData, TValue>({
    columns,
    data,
    searchColumnIds,
    searchPlaceholder = 'Search…',
    onRowClick,
    enableRowSelection,
    onSelectionChange,
    bulkActionsRender,
    defaultPageSize = 15,
    pageSizeOptions = [5, 10, 15, 25, 50],
}: PaginatedDataTableProps<TData, TValue>) {
    const id = useId();
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = useState<RowSelectionState>({});
    const [pagination, setPagination] = useState<PaginationState>({
        pageIndex: 0,
        pageSize: defaultPageSize,
    });

    const table = useReactTable({
        data,
        columns,
        state: {
            sorting,
            columnFilters,
            columnVisibility,
            rowSelection,
            pagination,
        },
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        onColumnVisibilityChange: setColumnVisibility,
        onRowSelectionChange: setRowSelection,
        onPaginationChange: setPagination,
        enableRowSelection: enableRowSelection ?? true,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
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
                            {table.getFilteredRowModel().rows.length} of {data.length} row
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
                            <TableRow key={headerGroup.id} className="hover:bg-transparent">
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead
                                            key={header.id}
                                            style={{ width: `${header.getSize()}px` }}
                                            className={cn('h-11', header.column.getSize() > 0 && header.column.getSize() < 100 && 'w-px', header.column.columnDef.meta?.className)}
                                        >
                                            {header.isPlaceholder ? null : header.column.getCanSort() ? (
                                                <div
                                                    className={cn(
                                                        header.column.getCanSort() &&
                                                            'flex h-full cursor-pointer items-center justify-between gap-2 select-none',
                                                    )}
                                                    onClick={header.column.getToggleSortingHandler()}
                                                    onKeyDown={(e) => {
                                                        if (header.column.getCanSort() && (e.key === 'Enter' || e.key === ' ')) {
                                                            e.preventDefault();
                                                            header.column.getToggleSortingHandler()?.(e);
                                                        }
                                                    }}
                                                    tabIndex={header.column.getCanSort() ? 0 : undefined}
                                                >
                                                    {flexRender(header.column.columnDef.header, header.getContext())}
                                                    {{
                                                        asc: <ChevronUpIcon className="shrink-0 opacity-60" size={16} aria-hidden="true" />,
                                                        desc: <ChevronDownIcon className="shrink-0 opacity-60" size={16} aria-hidden="true" />,
                                                    }[header.column.getIsSorted() as string] ?? null}
                                                </div>
                                            ) : (
                                                flexRender(header.column.columnDef.header, header.getContext())
                                            )}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows?.length ? (
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
                                    {row.getVisibleCells().map((cell) => {
                                        const columnSize = cell.column.getSize();
                                        const metaClassName = cell.column.columnDef.meta?.className;
                                        return (
                                            <TableCell
                                                key={cell.id}
                                                style={columnSize > 0 ? { width: `${columnSize}px` } : undefined}
                                                className={cn(columnSize > 0 && columnSize < 100 && 'w-px', metaClassName)}
                                            >
                                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                            </TableCell>
                                        );
                                    })}
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
            </div>

            <div className="flex flex-col items-center justify-between gap-4 sm:flex-row sm:gap-8">
                <div className="flex items-center gap-3">
                    <Label htmlFor={id} className="text-sm whitespace-nowrap max-sm:sr-only">
                        Rows per page
                    </Label>
                    <Select
                        value={table.getState().pagination.pageSize.toString()}
                        onValueChange={(value) => {
                            table.setPageSize(Number(value));
                        }}
                    >
                        <SelectTrigger id={id} className="h-8 w-fit whitespace-nowrap">
                            <SelectValue placeholder="Select number of results" />
                        </SelectTrigger>
                        <SelectContent className="[&_*[role=option]]:pr-8 [&_*[role=option]]:pl-2 [&_*[role=option]>span]:right-2 [&_*[role=option]>span]:left-auto">
                            {pageSizeOptions.map((pageSize) => (
                                <SelectItem key={pageSize} value={pageSize.toString()}>
                                    {pageSize}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div className="flex grow justify-center text-sm text-muted-foreground sm:justify-end">
                    <p className="text-sm whitespace-nowrap text-muted-foreground" aria-live="polite">
                        <span className="text-foreground">
                            {table.getState().pagination.pageIndex * table.getState().pagination.pageSize + 1}-
                            {Math.min(
                                Math.max(
                                    table.getState().pagination.pageIndex * table.getState().pagination.pageSize +
                                        table.getState().pagination.pageSize,
                                    0,
                                ),
                                table.getFilteredRowModel().rows.length,
                            )}
                        </span>{' '}
                        of <span className="text-foreground">{table.getFilteredRowModel().rows.length}</span>
                    </p>
                </div>

                <div>
                    <Pagination>
                        <PaginationContent>
                            <PaginationItem>
                                <Button
                                    size="icon"
                                    variant="outline"
                                    className="h-8 w-8 disabled:pointer-events-none disabled:opacity-50"
                                    onClick={() => table.firstPage()}
                                    disabled={!table.getCanPreviousPage()}
                                    aria-label="Go to first page"
                                >
                                    <ChevronFirstIcon className="size-4" aria-hidden="true" />
                                </Button>
                            </PaginationItem>

                            <PaginationItem>
                                <Button
                                    size="icon"
                                    variant="outline"
                                    className="h-8 w-8 disabled:pointer-events-none disabled:opacity-50"
                                    onClick={() => table.previousPage()}
                                    disabled={!table.getCanPreviousPage()}
                                    aria-label="Go to previous page"
                                >
                                    <ChevronLeftIcon className="size-4" aria-hidden="true" />
                                </Button>
                            </PaginationItem>

                            <PaginationItem>
                                <Button
                                    size="icon"
                                    variant="outline"
                                    className="h-8 w-8 disabled:pointer-events-none disabled:opacity-50"
                                    onClick={() => table.nextPage()}
                                    disabled={!table.getCanNextPage()}
                                    aria-label="Go to next page"
                                >
                                    <ChevronRightIcon className="size-4" aria-hidden="true" />
                                </Button>
                            </PaginationItem>

                            <PaginationItem>
                                <Button
                                    size="icon"
                                    variant="outline"
                                    className="h-8 w-8 disabled:pointer-events-none disabled:opacity-50"
                                    onClick={() => table.lastPage()}
                                    disabled={!table.getCanNextPage()}
                                    aria-label="Go to last page"
                                >
                                    <ChevronLastIcon className="size-4" aria-hidden="true" />
                                </Button>
                            </PaginationItem>
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    );
}
