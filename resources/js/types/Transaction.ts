import { User } from "./user";

export type Transaction = {
    id: number;
    sender: User;
    receiver: User;
    amount: number;
    commission_fee: number;
    created_at: string;
    updated_at: string;
}
