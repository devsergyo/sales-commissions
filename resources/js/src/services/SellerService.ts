import { ApiService } from './ApiService';
import type { ApiResponse } from '../interfaces/ApiResponse';
import type { Seller } from '../interfaces/Seller';

interface SellersResponse {
  sellers: Seller[];
}

interface DailyReportResponse {
  success: boolean;
  message: string;
}

export class SellerService extends ApiService {
  async getAll(): Promise<ApiResponse<SellersResponse>> {
    return this.get<SellersResponse>('/sellers');
  }

  async store(seller: Partial<Seller>): Promise<ApiResponse<Seller>> {
    return this.post<Seller>('/sellers/store', seller);
  }

  async update(id: number, seller: Partial<Seller>): Promise<ApiResponse<Seller>> {
    return this.put<Seller>(`/sellers/${id}`, seller);
  }

  async remove(id: number): Promise<ApiResponse<null>> {
    return this.delete(`/sellers/${id}`);
  }

  async getById(id: number): Promise<ApiResponse<Seller>> {
    return this.get<Seller>(`/sellers/${id}`);
  }
  
  async sendDailyReport(sellerId: number): Promise<ApiResponse<DailyReportResponse>> {
    return this.post<DailyReportResponse>(`/reports/${sellerId}/send-daily-report`, {});
  }
}

export const sellerService = new SellerService();
export default sellerService;
