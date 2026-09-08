export type Project = {
    id: number;
    user_id: number;
    name: string;
    slug: string;
    description: string | null;
    color: string;
    monitors_count?: number;
    webhooks_count?: number;
    active_monitors_count?: number;
    created_at: string;
    updated_at: string;
};

export type MonitorStatus = 'online' | 'offline' | 'degraded' | 'pending';
export type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE' | 'HEAD';

export type Monitor = {
    id: number;
    project_id: number;
    name: string;
    url: string;
    method: HttpMethod;
    headers?: Record<string, string> | null;
    body?: string | null;
    check_interval: number; // in minutes (e.g. 1, 5, 15, 30, 60)
    expected_status_code: number;
    timeout_seconds: number;
    status: MonitorStatus;
    last_checked_at: string | null;
    last_status_code: number | null;
    last_response_time_ms: number | null;
    uptime_percentage: number;
    is_active: boolean;
    next_check_at: string | null;
    created_at: string;
    updated_at: string;
    project?: Project;
    latest_results?: MonitorResult[];
};

export type MonitorResult = {
    id: number;
    monitor_id: number;
    status_code: number | null;
    response_time_ms: number | null;
    is_success: boolean;
    error_message: string | null;
    checked_at: string;
};

export type WebhookEndpoint = {
    id: number;
    project_id: number;
    name: string;
    token: string;
    ingest_url?: string;
    is_active: boolean;
    hmac_required: boolean;
    hmac_secret?: string | null;
    requests_count?: number;
    last_received_at: string | null;
    created_at: string;
    updated_at: string;
    project?: Project;
};

export type WebhookRequest = {
    id: number;
    webhook_endpoint_id: number;
    ip_address: string | null;
    ip_iso_code?: string | null;
    ip_country?: string | null;
    ip_city?: string | null;
    method: string;
    headers: Record<string, string[] | string>;
    query_params: Record<string, unknown> | null;
    payload: unknown;
    raw_body: string | null;
    content_type: string | null;
    received_at: string;
};

export type DashboardStats = {
    total_projects: number;
    total_monitors: number;
    online_monitors: number;
    offline_monitors: number;
    degraded_monitors: number;
    average_uptime: number;
    total_webhook_requests_today: number;
};
