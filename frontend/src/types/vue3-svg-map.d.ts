declare module 'vue3-svg-map' {
  import { Component } from 'vue'
  
  export interface Location {
    id: string
    name: string
    path: string
  }
  
  export interface Map {
    viewBox: string
    locations: Location[]
    label: string
    xmlns?: string
  }
  
  export const SvgMap: Component<{
    map: Map
    locationClass?: (location: Location, index: number) => string
    onClick?: (event: Event) => void
    onMouseover?: (event: Event) => void
    onMouseout?: (event: Event) => void
  }>
}

declare module '@/assets/maps/bangladesh.js' {
  import { Map } from 'vue3-svg-map'
  const map: Map
  export default map
}
