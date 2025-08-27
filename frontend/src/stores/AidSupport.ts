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

export interface Campaign {
  id: number | string
  name: string
  disaster_id: number | string
  disaster_name?: string
  ngo_id?: number | string
  ngo_name?: string
  status?: string
  help_needed?: string
}

export const useAidSupportStore = defineStore('aidSupport', {
  state: () => ({
    // data
    disasters: [] as Disaster[],
    campaigns: [] as Campaign[],

    // selections
    aidType: null as AidOption | null,
    disaster: null as Disaster | null,
    campaign: null as Campaign | null,

    // required extra field
    quantity: '' as string,
    description: '' as string,

    // ui state
    loading: false,
    error: null as string | null,
    message: null as string | null,
    ok: false,
  }),

  getters: {
    canSubmit(state) {
      const hasCore = !!(state.aidType && state.disaster && state.campaign)
      const hasQty  = state.quantity?.toString().trim().length > 0
      return hasCore && hasQty
    },
    defaultAidOptions(): AidOption[] {
      return [
        { label: 'FINANCIAL', value: 'financial', hint: 'Monetary help' },
        { label: 'MEDICAL',   value: 'medical',   hint: 'First-aid / blood' },
        { label: 'RESOURCES', value: 'resource',  hint: 'Food / goods / supplies' },
      ]
    },
  },

  actions: {
    setAidType(opt: AidOption | null) { this.aidType = opt; this.quantity = '' },
    setDisaster(d: Disaster | null)   { this.disaster = d; this.campaign = null; this.campaigns = [] },
    setCampaign(c: Campaign | null)   { this.campaign = c },
    setQuantity(v: string)    { this.quantity = v },
    setDescription(v: string) { this.description = v },

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

    async fetchCampaignsForSelected() {
      if (!this.disaster?.id) return
      this.loading = true; this.error = null
      try {
        // Get all active campaigns and filter by selected disaster_id
        const { data } = await api.get('/campaigns')
        const list = Array.isArray(data) ? data : (data?.data ?? [])
        this.campaigns = list.filter((c: Campaign) => String(c.disaster_id) === String(this.disaster!.id))
      } catch (e: any) {
        this.error = e?.response?.data?.message || e.message || 'Failed to load campaigns'
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
          campaign_id: this.campaign!.id,
          quantity: this.quantity,
          description: this.description || null,
          contact: null,
        })
        // reset after success
        this.aidType = null
        this.disaster = null
        this.campaign = null
        this.campaigns = []
        this.quantity = ''
        this.description = ''
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
