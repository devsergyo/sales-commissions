export interface Seller {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  fullname?: string; // Propriedade computada
  created_at: Date;
  updated_at: Date;
  deleted_at?: Date; // Devido ao uso de SoftDeletes
}
