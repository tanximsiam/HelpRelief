import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/lib/api'

export const useVolunteerTaskStore = defineStore('volunteerTask', () => {
  const hasActiveTask = ref(false)
  const canResign = ref(false)
  const loading = ref(false)
  const error = ref('')

  async function fetchActiveTask(userId: number) {
    loading.value = true
    error.value = ''
    try {
      // Fetch tasks assigned to user
      const tasksRes = await api.get(`/tasks?assigned_to=${userId}`)
      const logsRes = await api.get(`/task-logs?user_id=${userId}`)
      const now = new Date()
      // Find active tasks: exists in tasks but not in logs, or in logs but not checked out
      const activeTasks = tasksRes.data.filter((task: any) => {
        const log = logsRes.data.find((l: any) => l.task_id === task.id)
        if (!log) return true // assigned but no log yet
        return !log.check_out // log exists but not checked out
      })
      hasActiveTask.value = activeTasks.length > 0
      // Resign allowed if all logs are checked out or end_time is past
      canResign.value = logsRes.data.every((log: any) => log.check_out || (taskEndPast(log.task_end_time, now)))
    } catch {
      error.value = 'Failed to fetch active tasks.'
      hasActiveTask.value = false
      canResign.value = false
    } finally {
      loading.value = false
    }
  }

  function taskEndPast(endTime: string, now: Date) {
    if (!endTime) return false
    return new Date(endTime) < now
  }

  return { hasActiveTask, canResign, loading, error, fetchActiveTask }
})
