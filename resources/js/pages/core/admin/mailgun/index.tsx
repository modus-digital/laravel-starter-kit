import { Badge } from '@/shared/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui/table';
import AdminLayout from '@/shared/layouts/admin/layout';
import { type SharedData } from '@/types';
import { Head } from '@inertiajs/react';
import { format } from 'date-fns';
import { AlertTriangle, Mail, MailCheck, MailOpen, MailX, MousePointerClick, type LucideIcon } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Area, AreaChart, ResponsiveContainer, YAxis } from 'recharts';

type Stats = {
    total_sent: number;
    total_delivered: number;
    total_opened: number;
    total_clicked: number;
    total_bounced: number;
    total_failed: number;
};

type TrendDataPoint = {
    date: string;
    count: number;
};

type Trends = {
    sent: TrendDataPoint[];
    delivered: TrendDataPoint[];
    opened: TrendDataPoint[];
    clicked: TrendDataPoint[];
    bounced: TrendDataPoint[];
    failed: TrendDataPoint[];
};

type EmailMessage = {
    id: string;
    recipient: string;
    subject: string;
    status: string;
    created_at: string;
};

type EventBreakdown = {
    event: string;
    count: number;
};

type PageProps = SharedData & {
    stats: Stats;
    trends: Trends;
    recentMessages: EmailMessage[];
    eventBreakdown: EventBreakdown[];
};

type StatCardProps = {
    title: string;
    value: number;
    icon: LucideIcon;
    description: string;
    trendData: TrendDataPoint[];
    color: string;
};

function StatCard({ title, value, icon: Icon, description, trendData, color }: StatCardProps) {
    const hasData = trendData.some((point) => point.count > 0);
    const maxCount = Math.max(...trendData.map((p) => p.count), 1);

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">{title}</CardTitle>
                <Icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent className="space-y-2">
                <div className="text-2xl font-bold">{value}</div>
                <div className="h-[40px]">
                    <ResponsiveContainer width="100%" height="100%">
                        <AreaChart data={trendData} margin={{ top: 4, right: 0, left: 0, bottom: 0 }}>
                            <defs>
                                <linearGradient id={`gradient-${color}`} x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stopColor={color} stopOpacity={hasData ? 0.3 : 0.1} />
                                    <stop offset="100%" stopColor={color} stopOpacity={0} />
                                </linearGradient>
                            </defs>
                            <YAxis domain={[0, maxCount]} hide />
                            <Area
                                type="monotone"
                                dataKey="count"
                                stroke={color}
                                strokeWidth={2}
                                strokeOpacity={hasData ? 1 : 0.3}
                                fill={`url(#gradient-${color})`}
                                isAnimationActive={false}
                                baseValue={0}
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                </div>
                <p className="text-xs text-muted-foreground">{description}</p>
            </CardContent>
        </Card>
    );
}

export default function Index({ stats, trends, recentMessages, eventBreakdown }: PageProps) {
    const { t } = useTranslation();

    const statCards = [
        {
            title: t('admin.mailgun.stats.total_sent'),
            value: stats.total_sent,
            icon: Mail,
            description: t('admin.mailgun.stats.sent_description'),
            trendData: trends.sent,
            color: '#3b82f6', // blue
        },
        {
            title: t('admin.mailgun.stats.total_delivered'),
            value: stats.total_delivered,
            icon: MailCheck,
            description: t('admin.mailgun.stats.delivered_description'),
            trendData: trends.delivered,
            color: '#22c55e', // green
        },
        {
            title: t('admin.mailgun.stats.total_opened'),
            value: stats.total_opened,
            icon: MailOpen,
            description: t('admin.mailgun.stats.opened_description'),
            trendData: trends.opened,
            color: '#8b5cf6', // purple
        },
        {
            title: t('admin.mailgun.stats.total_clicked'),
            value: stats.total_clicked,
            icon: MousePointerClick,
            description: t('admin.mailgun.stats.clicked_description'),
            trendData: trends.clicked,
            color: '#06b6d4', // cyan
        },
        {
            title: t('admin.mailgun.stats.total_bounced'),
            value: stats.total_bounced,
            icon: MailX,
            description: t('admin.mailgun.stats.bounced_description'),
            trendData: trends.bounced,
            color: '#f97316', // orange
        },
        {
            title: t('admin.mailgun.stats.total_failed'),
            value: stats.total_failed,
            icon: AlertTriangle,
            description: t('admin.mailgun.stats.failed_description'),
            trendData: trends.failed,
            color: '#ef4444', // red
        },
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'delivered':
                return 'default';
            case 'bounced':
            case 'failed':
                return 'destructive';
            case 'sent':
                return 'secondary';
            default:
                return 'outline';
        }
    };

    return (
        <AdminLayout>
            <Head title={t('admin.mailgun.title')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div>
                        <h1 className="text-2xl font-semibold">{t('admin.mailgun.title')}</h1>
                        <p className="text-sm text-muted-foreground">{t('admin.mailgun.description')}</p>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {statCards.map((stat, index) => (
                            <StatCard key={index} {...stat} />
                        ))}
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('admin.mailgun.recent_messages')}</CardTitle>
                                <CardDescription>{t('admin.mailgun.recent_messages_description')}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                {recentMessages.length === 0 ? (
                                    <div className="py-8 text-center text-sm text-muted-foreground">{t('admin.mailgun.no_messages')}</div>
                                ) : (
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>{t('admin.mailgun.table.recipient')}</TableHead>
                                                <TableHead>{t('admin.mailgun.table.subject')}</TableHead>
                                                <TableHead>{t('admin.mailgun.table.status')}</TableHead>
                                                <TableHead>{t('admin.mailgun.table.sent_at')}</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {recentMessages.map((message) => (
                                                <TableRow key={message.id}>
                                                    <TableCell className="font-medium">{message.recipient}</TableCell>
                                                    <TableCell className="max-w-xs truncate">{message.subject}</TableCell>
                                                    <TableCell>
                                                        <Badge variant={getStatusColor(message.status)}>{message.status}</Badge>
                                                    </TableCell>
                                                    <TableCell>{format(new Date(message.created_at), 'MMM d, HH:mm')}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{t('admin.mailgun.event_breakdown')}</CardTitle>
                                <CardDescription>{t('admin.mailgun.event_breakdown_description')}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                {eventBreakdown.length === 0 ? (
                                    <div className="py-8 text-center text-sm text-muted-foreground">{t('admin.mailgun.no_events')}</div>
                                ) : (
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>{t('admin.mailgun.table.event')}</TableHead>
                                                <TableHead className="text-right">{t('admin.mailgun.table.count')}</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {eventBreakdown.map((event, index) => (
                                                <TableRow key={index}>
                                                    <TableCell className="font-medium">
                                                        <Badge variant="outline">{event.event}</Badge>
                                                    </TableCell>
                                                    <TableCell className="text-right">{event.count}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
