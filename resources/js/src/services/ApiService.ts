import axios from 'axios';
import type { AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';
import type { ApiResponse } from '../interfaces/ApiResponse';

export abstract class ApiService {
  protected api: AxiosInstance;
  protected token: string | null = null;

  constructor(baseURL?: string) {

    this.api = axios.create({
      baseURL: baseURL || import.meta.env.VITE_API_URL || 'http://localhost/api',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    this.api.interceptors.request.use((config) => {
      // Sempre obter o token mais recente do localStorage
      const currentToken = localStorage.getItem('token');
      if (currentToken) {
        config.headers['Authorization'] = `Bearer ${currentToken}`;
      }
      return config;
    });
    this.token = localStorage.getItem('token');
  }

  setToken(token: string): void {
    this.token = token;
    localStorage.setItem('token', token);
  }

  clearToken(): void {
    this.token = null;
    localStorage.removeItem('token');
  }

  protected async request<T>(
    method: string,
    url: string,
    data?: any,
    config?: AxiosRequestConfig
  ): Promise<ApiResponse<T>> {
    try {
      const response: AxiosResponse<ApiResponse<T>> = await this.api.request({
        method,
        url,
        data,
        ...config
      });
      return response.data;
    } catch (error: any) {
      if (error.response) {
        return error.response.data;
      } else {
        return {
          success: false,
          message: error.message || 'Erro ao conectar com o servidor',
          data: null as unknown as T
        };
      }
    }
  }

  async get<T>(url: string, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    return this.request('get', url, undefined, config);
  }

  async post<T>(url: string, data?: any, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    return this.request('post', url, data, config);
  }

  async put<T>(url: string, data?: any, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    return this.request('put', url, data, config);
  }

  async delete<T>(url: string, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    return this.request('delete', url, undefined, config);
  }
}
