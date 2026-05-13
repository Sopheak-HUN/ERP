import { reactive, computed } from 'vue'

const layoutConfig = reactive({
  preset: 'Aura',
  primary: 'emerald',
  surface: null,
  darkTheme: false,
  menuMode: 'static'
})

const layoutState = reactive({
  staticMenuInactive: false,
  overlayMenuActive: false,
  profileSidebarVisible: false,
  configSidebarVisible: false,
  mobileMenuActive: false,
  menuHoverActive: false,
  activePath: null
})

export const useLayout = () => {
  const toggleMenu = () => {
    if (window.innerWidth > 991) {
      if (layoutConfig.menuMode === 'static') {
        layoutState.staticMenuInactive = !layoutState.staticMenuInactive
      } else if (layoutConfig.menuMode === 'overlay') {
        layoutState.overlayMenuActive = !layoutState.overlayMenuActive
      }
    } else {
      layoutState.mobileMenuActive = !layoutState.mobileMenuActive
    }
  }

  const isDarkTheme = computed(() => layoutConfig.darkTheme)

  return {
    layoutConfig,
    layoutState,
    toggleMenu,
    isDarkTheme
  }
}
