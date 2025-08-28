import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/lib/api'

export const useVolunteerTaskStore = defineStore('volunteerTask', () => {
  const hasActiveTask = ref(false)
  const loading = ref(false)
  const error = ref('')

  async function fetchActiveTask(userId: number) {
    loading.value = true
    error.value = ''
    try {
      const res = await api.get(`/task-logs?user_id=${userId}`)
      hasActiveTask.value = res.data.some((log: {
        status: string,
        check_in: string | null,
        check_out: string | null,
        task_end_time: string
      }) => {
        return log.status === 'started'
      })
    } catch {
      error.value = 'Failed to fetch active tasks.'
      hasActiveTask.value = false
    } finally {
      loading.value = false
    }
  }

  return { hasActiveTask, loading, error, fetchActiveTask }
})
