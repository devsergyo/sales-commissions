import { Seller } from './Seller';

export interface Sale {
  id: number;
  seller_id: number;
  amount: number;
  commission: number;
  sale_date: Date;
  seller?: Seller; // Relacionamento belongsTo
  created_at: Date;
  updated_at: Date;
  deleted_at?: Date; // Devido ao uso de SoftDeletes
}
