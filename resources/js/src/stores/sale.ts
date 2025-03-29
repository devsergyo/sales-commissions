import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Sale } from '../interfaces/Sale';
import { saleService } from '../services/SaleService';

export const useSaleStore = defineStore('sale', () => {
  const sales = ref<Sale[]>([]);
  const currentSale = ref<Sale | null>(null);
  const loading = ref(false);
  const error = ref('');

  const getAllSales = async () => {
    loading.value = true;
    error.value = '';
    console.log('Iniciando busca de vendas...');
    try {
      console.log('Chamando API para buscar vendas...');
      const response = await saleService.getAll();
      console.log('Resposta da API:', response);
      if (response.success) {
        sales.value = response.data.sales;
        console.log('Vendas carregadas:', sales.value);
      } else {
        error.value = response.message;
        console.error('Erro retornado pela API:', response.message);
      }
    } catch (e) {
      console.error('Exceção ao carregar vendas:', e);
      error.value = 'Erro ao carregar vendas';
    } finally {
      loading.value = false;
    }
  };

  const getSalesBySeller = async (sellerId: number) => {
    loading.value = true;
    error.value = '';
    try {
      console.log(`Buscando vendas do vendedor ID: ${sellerId}`);
      const response = await saleService.getBySeller(sellerId);
      if (response.success) {
        sales.value = response.data.sales;
        console.log(`Vendas do vendedor ${sellerId} carregadas:`, sales.value);
      } else {
        error.value = response.message;
        console.error('Erro retornado pela API:', response.message);
      }
    } catch (e) {
      console.error('Exceção ao carregar vendas do vendedor:', e);
      error.value = 'Erro ao carregar vendas do vendedor';
    } finally {
      loading.value = false;
    }
  };

  const createSale = async (sale: Partial<Sale>) => {
    loading.value = true;
    error.value = '';
    try {
      console.log('Criando nova venda:', sale);
      const response = await saleService.store(sale);
      if (response.success) {
        await getAllSales();
        return { success: true, data: response.data };
      } else {
        error.value = response.message;
        return { success: false, message: response.message };
      }
    } catch (e) {
      console.error('Exceção ao criar venda:', e);
      error.value = 'Erro ao criar venda';
      return { success: false, message: 'Erro ao criar venda' };
    } finally {
      loading.value = false;
    }
  };
  
  const hasError = computed(() => error.value !== '');
  
  const clearCurrentSale = () => {
    currentSale.value = null;
  };

  return {
    sales,
    currentSale,
    loading,
    error,
    hasError,
    getAllSales,
    getSalesBySeller,
    createSale,
    clearCurrentSale
  };
});
