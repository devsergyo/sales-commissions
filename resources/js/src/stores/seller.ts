import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Seller } from '../interfaces/Seller';
import { sellerService } from '../services/SellerService';

export const useSellerStore = defineStore('seller', () => {
  const sellers = ref<Seller[]>([]);
  const currentSeller = ref<Seller | null>(null);
  const loading = ref(false);
  const error = ref('');

  const getAllSellers = async () => {
    loading.value = true;
    error.value = '';
    console.log('Iniciando busca de vendedores...');
    try {
      console.log('Chamando API para buscar vendedores...');
      const response = await sellerService.getAll();
      console.log('Resposta da API:', response);
      if (response.success) {
        sellers.value = response.data.sellers;
        console.log('Vendedores carregados:', sellers.value);
      } else {
        error.value = response.message;
        console.error('Erro retornado pela API:', response.message);
      }
    } catch (e) {
      console.error('Exceção ao carregar vendedores:', e);
      error.value = 'Erro ao carregar vendedores';
    } finally {
      loading.value = false;
    }
  };

  const getSellerById = async (id: number) => {
    loading.value = true;
    error.value = '';
    try {
      const response = await sellerService.getById(id);
      if (response.success) {
        currentSeller.value = response.data;
      } else {
        error.value = response.message;
      }
    } catch (e) {
      error.value = 'Erro ao carregar dados do vendedor';
    } finally {
      loading.value = false;
    }
  };

  const createSeller = async (seller: Partial<Seller>) => {
    loading.value = true;
    error.value = '';
    try {
      const response = await sellerService.store(seller);
      if (response.success) {
        await getAllSellers();
        return { success: true, data: response.data };
      } else {
        error.value = response.message;
        return { success: false, message: response.message };
      }
    } catch (e) {
      error.value = 'Erro ao criar vendedor';
      return { success: false, message: 'Erro ao criar vendedor' };
    } finally {
      loading.value = false;
    }
  };

  const updateSeller = async (id: number, seller: Partial<Seller>) => {
    loading.value = true;
    error.value = '';
    try {
      const response = await sellerService.update(id, seller);
      if (response.success) {
        await getAllSellers();
        return { success: true, data: response.data };
      } else {
        error.value = response.message;
        return { success: false, message: response.message };
      }
    } catch (e) {
      error.value = 'Erro ao atualizar vendedor';
      return { success: false, message: 'Erro ao atualizar vendedor' };
    } finally {
      loading.value = false;
    }
  };

  const deleteSeller = async (id: number) => {
    loading.value = true;
    error.value = '';
    try {
      const response = await sellerService.remove(id);
      if (response.success) {
        await getAllSellers();
        return { success: true };
      } else {
        error.value = response.message;
        return { success: false, message: response.message };
      }
    } catch (e) {
      error.value = 'Erro ao excluir vendedor';
      return { success: false, message: 'Erro ao excluir vendedor' };
    } finally {
      loading.value = false;
    }
  };

  const sendDailyReport = async (sellerId: number) => {
    loading.value = true;
    error.value = '';
    try {
      const response = await sellerService.sendDailyReport(sellerId);
      return response.data;
    } catch (e) {
      error.value = `Erro ao enviar relatório para vendedor #${sellerId}`;
      console.error(`Erro ao enviar relatório para vendedor #${sellerId}:`, e);
      throw e;
    } finally {
      loading.value = false;
    }
  };

  const hasError = computed(() => error.value !== '');

  const clearCurrentSeller = () => {
    currentSeller.value = null;
  };

  return {
    sellers,
    currentSeller,
    loading,
    error,
    hasError,
    getAllSellers,
    getSellerById,
    createSeller,
    updateSeller,
    deleteSeller,
    sendDailyReport,
    clearCurrentSeller
  };
});
