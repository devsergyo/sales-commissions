import { ApiService } from './ApiService';
import type { ApiResponse } from '../interfaces/ApiResponse';
import type { Sale } from '../interfaces/Sale';

interface SalesResponse {
  sales: Sale[];
}

export class SaleService extends ApiService {
  async getAll(): Promise<ApiResponse<SalesResponse>> {
    return this.get<SalesResponse>('/sales');
  }

  async getBySeller(sellerId: number): Promise<ApiResponse<SalesResponse>> {
    return this.get<SalesResponse>(`/sales/${sellerId}/sales`);
  }

  async store(sale: Partial<Sale>): Promise<ApiResponse<Sale>> {
    return this.post<Sale>('/sales/store', sale);
  }
}

export const saleService = new SaleService();
