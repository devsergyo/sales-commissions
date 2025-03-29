import { ApiService } from './ApiService';
import type { ApiResponse, LoginResponse } from '../interfaces/ApiResponse';
import type { User } from '../interfaces/User';

export class AuthService extends ApiService {
  private endpoint = '/auth';
  private userKey = 'user_data';
  private currentUser: User | null = null;

  async login(email: string, password: string): Promise<ApiResponse<LoginResponse>> {
    console.log('Tentando login com:', { email, password });
    try {
      const response = await this.post<LoginResponse>(`${this.endpoint}/login`, { email, password });
      console.log('Resposta do login:', response);
      
      if (response.success) {
        this.setToken(response.data.token);
        
        const userData: User = {
          id: response.data.id,
          name: response.data.name,
          email: response.data.email
        };
        
        this.setUserData(userData);
      }
      
      return response;
    } catch (error) {
      console.error('Erro durante login:', error);
      throw error;
    }
  }

  async logout(): Promise<ApiResponse<null>> {
    const response = await this.post<null>(`${this.endpoint}/logout`);
    if (response.success) {
      this.clearToken();
      this.clearUserData();
    }
    return response;
  }

  async getUser(): Promise<ApiResponse<User>> {
    const savedUser = this.getUserData();
    
    if (savedUser) {
      return {
        success: true,
        message: 'Dados do usuário obtidos do armazenamento local',
        data: savedUser
      };
    }
    
    try {
      console.log('Buscando dados do usuário');
      return this.get<User>(`${this.endpoint}/user`);
    } catch (error) {
      console.error('Erro ao buscar dados do usuário:', error);
      throw error;
    }
  }

  setUserData(user: User): void {
    this.currentUser = user;
    localStorage.setItem(this.userKey, JSON.stringify(user));
  }

  getUserData(): User | null {
    if (this.currentUser) {
      return this.currentUser;
    }
    
    const userData = localStorage.getItem(this.userKey);
    if (userData) {
      try {
        this.currentUser = JSON.parse(userData);
        return this.currentUser;
      } catch (e) {
        return null;
      }
    }
    return null;
  }

  clearUserData(): void {
    this.currentUser = null;
    localStorage.removeItem(this.userKey);
  }

  isAuthenticated(): boolean {
    return !!this.token;
  }
}

export const authService = new AuthService();
