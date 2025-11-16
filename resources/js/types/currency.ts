import { Country } from "./country";

export type Currency = {
    id: number;
    country: Country
    name: string;
    name_plural: string;
    code: string;
    symbol: string;
    created_at: string;
    updated_at: string;
}
