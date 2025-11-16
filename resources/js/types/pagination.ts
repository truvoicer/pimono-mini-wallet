export interface Pagination {
    data: Record<string, unknown>[];
    meta: {
        total_pages: number;
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
        path: string;
        links: {
            url: string | null;
            active: boolean;
            label: string;
            page?: number | null;
        }[];
    };
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
};
