import { createRouter, createWebHashHistory } from '@ionic/vue-router';
import TabsLayout from './TabsLayout.vue';
import LoginScreen from './screens/LoginScreen.vue';
import HomeScreen from './screens/HomeScreen.vue';
import UndanganScreen from './screens/UndanganScreen.vue';
import BudgetScreen from './screens/BudgetScreen.vue';
import PlannerScreen from './screens/PlannerScreen.vue';
import MoreScreen from './screens/MoreScreen.vue';

const routes = [
  { path: '/', redirect: '/tabs/home' },
  { path: '/login', component: LoginScreen },
  {
    path: '/tabs/',
    component: TabsLayout,
    children: [
      { path: '', redirect: '/tabs/home' },
      { path: 'home', component: HomeScreen },
      { path: 'undangan', component: UndanganScreen },
      { path: 'budget', component: BudgetScreen },
      { path: 'planner', component: PlannerScreen },
      { path: 'more', component: MoreScreen },
    ],
  },
];

export const router = createRouter({
  history: createWebHashHistory(),
  routes,
});
