import {reactive} from 'vue'

/* Visibility of the app chrome. reactive() makes these reactive in any
   component that reads them through a computed property. */
export const layout = reactive({
    isNavBarVisible: true,
    isFooterBarVisible: true,
    isAsideVisible: true,
    isAsideMobileExpanded: false
})

export function toggleAsideMobile(show = null) {
    const isShow = show !== null ? show : !layout.isAsideMobileExpanded

    document.documentElement.classList.toggle('has-aside-mobile-expanded', isShow)
    layout.isAsideMobileExpanded = isShow
}

export function toggleAsideDesktopOnly(show = null) {
    const className = 'has-aside-desktop-only-visible'
    const classList = document.documentElement.classList

    if (show === null) {
        classList.toggle(className)
    } else {
        classList.toggle(className, show)
    }
}

export function setFullPage(fullPage) {
    layout.isNavBarVisible = !fullPage
    layout.isAsideVisible = !fullPage
    layout.isFooterBarVisible = !fullPage

    document.documentElement.classList[fullPage ? 'remove' : 'add'](
        'has-aside-left', 'has-navbar-fixed-top')
}
