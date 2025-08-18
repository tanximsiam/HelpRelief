import { defineStore } from 'pinia'
import { api } from '@/lib/api'

export interface AidOption {
  label: string
  value: 'physical' | 'financial' | 'medical' | 'food' | string
  hint?: string
}
export interface Disaster {
  id: number | string
  name: string
  location?: string
  since?: string
  severity?: string
  severityLabel?: string
  meta?: Record<string, unknown>
}
export interface Ngo {
  id: number | string
  name: string
  campaign_title?: string
  description?: string
  logoUrl?: string
}

export const useAidSupportStore = defineStore('aidSupport', {
  state: () => ({
    // data
    disasters: [] as Disaster[],
    ngos: [] as Ngo[],

    // selections
    aidType: null as AidOption | null,
    disaster: null as Disaster | null,
    ngo: null as Ngo | null,

    // required extra field
    quantity: '' as string,

    // ui state
    loading: false,
    error: null as string | null,
    message: null as string | null,
    ok: false,
  }),

  getters: {
    canSubmit(state) {
      const hasCore = !!(state.aidType && state.disaster && state.ngo)
      const hasQty  = state.quantity?.toString().trim().length > 0
      return hasCore && hasQty
    },
    defaultAidOptions(): AidOption[] {
      return [
        { label: 'FINANCIAL', value: 'financial', hint: 'Monetary help' },
        { label: 'MEDICAL',   value: 'medical',   hint: 'First-aid / blood' },
        { label: 'FOOD',      value: 'food',      hint: 'Meals & supplies' },
      ]
    },
  },

  actions: {
    setAidType(opt: AidOption | null) { this.aidType = opt; this.quantity = '' },
    setDisaster(d: Disaster | null)   { this.disaster = d; this.ngo = null; this.ngos = [] },
    setNgo(n: Ngo | null)             { this.ngo = n },
    setQuantity(v: string)    { this.quantity = v },

    async fetchDisasters() {
      if (this.disasters.length) return
      this.loading = true; this.error = null
      try {
        const { data } = await api.get('/active-disasters')
        this.disasters = Array.isArray(data) ? data : (data?.data ?? [])
      } catch (e: any) {
        this.error = e?.response?.data?.message || e.message || 'Failed to load disasters'
      } finally { this.loading = false }
    },

    async fetchNgosForSelected() {
      if (!this.disaster?.id) return
      this.loading = true; this.error = null
      try {
        const { data } = await api.get('/ngos', { params: { disaster_id: this.disaster.id } })
        this.ngos = Array.isArray(data) ? data : (data?.data ?? [])
      } catch (e: any) {
        this.error = e?.response?.data?.message || e.message || 'Failed to load NGOs'
      } finally { this.loading = false }
    },

    async submitAidSupport() {
      if (!this.canSubmit) {
        this.ok = false
        this.message = this.aidType?.value === 'financial'
          ? 'Please enter the amount (BDT) before confirming.'
          : 'Please enter the quantity before confirming.'
        return
      }
      this.loading = true; this.error = null; this.message = null; this.ok = false
      try {
        await api.post('/aid-supports', {
          aid_type: this.aidType!.value,
          disaster_id: this.disaster!.id,
          ngo_id: this.ngo!.id,
          quantity: this.quantity,
          description: null,
          contact_info: null,
        })
        // reset after success
        this.aidType = null
        this.disaster = null
        this.ngo = null
        this.ngos = []
        this.quantity = ''
        this.ok = true
        this.message = '✅ Aid support submitted! Thank you.'
      } catch (e: any) {
        this.ok = false
        this.error = e?.response?.data?.message || e.message || 'Submission failed'
        this.message = this.error
        throw e
      } finally { this.loading = false }
    },
  },
})
