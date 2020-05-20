import VueRouter from 'vue-router'
import tasksView from './components/TasksView'
import task from '@/components/Task'
import addTask from '@/components/TaskAdd'
import EventBus from '@/EventBus'
const router = new VueRouter({})

router.beforeEach((to, from, next) => {
  if (from.name === `task.show` && to.name === `task.show`) { EventBus.fire(`force-refresh-task`) }

  if (from.name === `task.show` || to.name === `task.show`) { EventBus.fire(`on-transition`) } else { EventBus.fire(`off-transition`) }

  if (from.name === `task.add` || to.name === `task.add`) { EventBus.fire(`addTask-open`) }

  if (process.env.NODE_ENV === `test`) { EventBus.clearHistory() }

  next()
})

router.afterEach((to, from) => {
  const switchLangAnchor = document.querySelector(`.switchLangAnchor`)
  if (!switchLangAnchor) { return }

  if (!switchLangAnchor.dataset.originalHref) { switchLangAnchor.dataset.originalHref = switchLangAnchor.href }

  switchLangAnchor.href = switchLangAnchor.dataset.originalHref + `?returnUrl=` + to.path
})

const routes = [
  { path: `/task/:id`, component: task, props: true, name: `task.show`, },
  { path: `/addTask/:type`, component: addTask, props: true, name: `task.add`, },
  { path: `/*`, component: tasksView, props: false, name: `task.index`, }, ]

router.addRoutes(routes)

export { routes }

export default router
