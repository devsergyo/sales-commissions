import type { RouteLocationNormalized, NavigationGuardNext } from 'vue-router'
import { authService } from '../services/AuthService'

export function authMiddleware(to: RouteLocationNormalized, _: RouteLocationNormalized, next: NavigationGuardNext) {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const isAuthenticated = authService.isAuthenticated()

  if (requiresAuth && !isAuthenticated) {
    next({ name: 'login' })
  } else if (to.path === '/login' && isAuthenticated) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
}
