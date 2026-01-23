import { Building2, Search, User as UserIcon } from 'lucide-react';
import { SidebarGroup, SidebarGroupContent, useSidebar } from './ui/sidebar';
import { useTranslation } from 'react-i18next';
import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/components/ui/command';
import { router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { NavItem, SearchResult } from '@/types';

export function AppSearchBar({ mainNavItems, footerNavItems }: { mainNavItems: NavItem[]; footerNavItems: NavItem[] }) {
    const { t } = useTranslation();
    const { state } = useSidebar();
    const [commandOpen, setCommandOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<SearchResult[]>([]);
    const [isSearching, setIsSearching] = useState(false);

    const isCollapsed = state === 'collapsed';

    // Helper to extract URL string from NavItem href
    const getHrefUrl = (href: NavItem['href']): string => {
        if (typeof href === 'string') return href;
        return href.url;
    };

    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
                e.preventDefault();
                setCommandOpen((open) => !open);
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, []);

    // Debounced search effect
    useEffect(() => {
        if (!searchQuery.trim()) {
            setSearchResults([]);
            setIsSearching(false);
            return;
        }

        setIsSearching(true);
        const timeoutId = setTimeout(() => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(`/search?q=${encodeURIComponent(searchQuery)}&limit=10`, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
            })
                .then((res) => res.json())
                .then((data) => {
                    setSearchResults(data.data || []);
                    setIsSearching(false);
                })
                .catch(() => {
                    setSearchResults([]);
                    setIsSearching(false);
                });
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [searchQuery]);

    // Reset search when dialog closes
    useEffect(() => {
        if (!commandOpen) {
            setSearchQuery('');
            setSearchResults([]);
        }
    }, [commandOpen]);

    // Group results by type
    const groupedResults = useMemo(() => {
        const groups: Record<string, SearchResult[]> = {};

        searchResults.forEach((result) => {
            if (!groups[result.type]) {
                groups[result.type] = [];
            }
            groups[result.type].push(result);
        });

        return groups;
    }, [searchResults]);

    // Get icon for result type
    const getTypeIcon = (type: string) => {
        switch (type) {
            case 'User':
                return UserIcon;
            case 'Client':
                return Building2;
            default:
                return null;
        }
    };

    const hasSearchQuery = searchQuery.trim().length > 0;
    const hasSearchResults = searchResults.length > 0;

    // Filter navigation items based on search query
    const filteredMainNavItems = useMemo(() => {
        if (!hasSearchQuery) return mainNavItems;
        const query = searchQuery.toLowerCase();
        return mainNavItems.filter((item) => item.title.toLowerCase().includes(query));
    }, [mainNavItems, searchQuery, hasSearchQuery]);

    const filteredFooterNavItems = useMemo(() => {
        if (!hasSearchQuery) return footerNavItems;
        const query = searchQuery.toLowerCase();
        return footerNavItems.filter((item) => item.title.toLowerCase().includes(query));
    }, [footerNavItems, searchQuery, hasSearchQuery]);

    const hasFilteredNavItems = filteredMainNavItems.length > 0 || filteredFooterNavItems.length > 0;

    return (
        <>
            <SidebarGroup className="py-0">
                <SidebarGroupContent>
                    {isCollapsed ? (
                        <button
                            onClick={() => setCommandOpen(true)}
                            className="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <Search className="h-4 w-4" />
                        </button>
                    ) : (
                        <button
                            onClick={() => setCommandOpen(true)}
                            className="flex h-9 w-full items-center gap-2 rounded-md border border-input bg-background px-3 text-sm text-muted-foreground shadow-xs transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <Search className="h-4 w-4" />
                            <span className="flex-1 text-left">{t('common.search' as never)}</span>
                            <kbd className="pointer-events-none hidden h-5 select-none items-center gap-1 rounded border bg-muted px-1.5 font-mono text-[10px] font-medium text-muted-foreground sm:flex">
                                <span className="text-xs">⌘</span>K
                            </kbd>
                        </button>
                    )}
                </SidebarGroupContent>
            </SidebarGroup>

            <CommandDialog
                open={commandOpen}
                onOpenChange={setCommandOpen}
                title={t('common.command_palette' as never)}
                description={t('common.search_commands' as never)}
                shouldFilter={!hasSearchQuery}
            >
                <CommandInput placeholder={t('common.type_to_search' as never)} value={searchQuery} onValueChange={setSearchQuery} />
                <CommandList>
                    {/* Only show empty state when searching and no results found anywhere */}
                    {hasSearchQuery && !hasSearchResults && !hasFilteredNavItems && (
                        <CommandEmpty>
                            {isSearching ? (t('common.searching' as never) || 'Searching...') : t('common.no_results' as never)}
                        </CommandEmpty>
                    )}

                    {/* Search Results */}
                    {hasSearchQuery &&
                        hasSearchResults &&
                        Object.entries(groupedResults).map(([type, results]) => {
                            const IconComponent = getTypeIcon(type);
                            return (
                                <CommandGroup key={type} heading={type}>
                                    {results.map((result) => (
                                        <CommandItem
                                            key={`${type}-${result.id}`}
                                            onSelect={() => {
                                                router.visit(result.url);
                                                setCommandOpen(false);
                                            }}
                                        >
                                            {IconComponent && <IconComponent />}
                                            <span>{result.label}</span>
                                            {result.subtitle && <span className="ml-auto text-xs text-muted-foreground">{result.subtitle}</span>}
                                        </CommandItem>
                                    ))}
                                </CommandGroup>
                            );
                        })}

                    {/* Navigation Items (always shown, filtered when searching) */}
                    {filteredMainNavItems.length > 0 && (
                        <>
                            {hasSearchQuery && hasSearchResults && <CommandSeparator />}
                            <CommandGroup heading={t('navigation.labels.navigation' as never)}>
                                {filteredMainNavItems.map((item) => (
                                    <CommandItem
                                        key={getHrefUrl(item.href)}
                                        onSelect={() => {
                                            router.visit(item.href);
                                            setCommandOpen(false);
                                        }}
                                    >
                                        {item.icon && <item.icon />}
                                        <span>{item.title}</span>
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </>
                    )}

                    {filteredFooterNavItems.length > 0 && (
                        <>
                            <CommandSeparator />
                            <CommandGroup heading={t('common.quick_actions' as never)}>
                                {filteredFooterNavItems.map((item) => (
                                    <CommandItem
                                        key={getHrefUrl(item.href)}
                                        onSelect={() => {
                                            router.visit(item.href);
                                            setCommandOpen(false);
                                        }}
                                    >
                                        {item.icon && <item.icon />}
                                        <span>{item.title}</span>
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </>
                    )}
                </CommandList>
            </CommandDialog>
        </>
    );
}
