import { createRouter, createWebHashHistory } from 'vue-router';
import DashboardView from '../views/Dashboard/DashboardView.vue';
import SellerListView from '../views/Seller/SellerListView.vue';
import SellerFormView from '../views/Seller/SellerFormView.vue';

const routes = [
    { 
        path: '/', 
        component: DashboardView,
        meta: { requiresAuth: true }
    },
    { 
        path: '/dashboard', 
        component: DashboardView,
        meta: { requiresAuth: true }
    },
    { 
        path: '/login', 
        component: () => import('../views/Auth/LoginView.vue'),
        name: 'login'
    },
    { 
        path: '/vendedores', 
        component: SellerListView,
        name: 'seller-list',
        meta: { requiresAuth: true }
    },
    { 
        path: '/vendedores/novo', 
        component: SellerFormView,
        name: 'seller-create',
        meta: { requiresAuth: true }
    },
    { 
        path: '/vendedores/:id', 
        component: SellerFormView,
        name: 'seller-edit',
        props: true,
        meta: { requiresAuth: true }
    },
    { 
        path: '/vendas', 
        component: () => import('../views/Sale/SaleListView.vue'),
        name: 'sales-list',
        meta: { requiresAuth: true }
    },
    { 
        path: '/vendas/nova', 
        component: () => import('../views/Sale/SaleFormView.vue'),
        name: 'sales-create',
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

router.beforeEach((to, _, next) => {
    const token = localStorage.getItem('token');
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    
    if (requiresAuth && !token) {
        next('/login');
    } else if (to.path === '/login' && token) {
        next('/dashboard');
    } else {
        next();
    }
});

export default router;
