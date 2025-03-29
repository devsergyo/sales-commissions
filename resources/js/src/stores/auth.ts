import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { User } from '../interfaces/ApiResponse'
import { authService } from '../services/AuthService'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const isAuthenticated = ref(false)

  function setUserAndToken(userData: User, userToken: string) {
    user.value = userData
    token.value = userToken
    isAuthenticated.value = true
    localStorage.setItem('token', userToken)
    localStorage.setItem('user_data', JSON.stringify(userData))
    authService.setToken(userToken)
  }

  function clearUserAndToken() {
    user.value = null
    token.value = null
    isAuthenticated.value = false
    localStorage.removeItem('token')
    localStorage.removeItem('user_data')
    authService.clearToken()
  }

  async function login(email: string, password: string) {
    try {
      const response = await authService.login(email, password)
      
      if (response.success && response.data) {
        const userData: User = {
          id: response.data.id,
          name: response.data.name,
          email: response.data.email
        }
        
        setUserAndToken(userData, response.data.token)
      }
      
      return response
    } catch (error) {
      throw error
    }
  }

  async function logout() {
    try {
      const response = await authService.logout()
      clearUserAndToken()
      return response
    } catch (error) {
      console.error('Erro no logout:', error)
      clearUserAndToken()
      throw error
    }
  }

  function loadUserFromStorage() {
    const storedToken = localStorage.getItem('token')
    const storedUser = localStorage.getItem('user_data')
    
    if (storedToken && storedUser) {
      try {
        const userJson = JSON.parse(storedUser)
        
        token.value = storedToken
        user.value = userJson
        isAuthenticated.value = true
        authService.setToken(storedToken)
        return true
      } catch (e) {
        clearUserAndToken()
        return false
      }
    }
    return false
  }

  return {
    user,
    token,
    isAuthenticated,
    login,
    logout,
    loadUserFromStorage,
    clearUserAndToken
  }
})
