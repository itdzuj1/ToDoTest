/**
 * Protects /admin routes with cookie session checks.
 * CSRF, session restore and intended-route redirect land in Slice 5.
 */
export default defineNuxtRouteMiddleware(() => {
    // Stub: no redirect until useAdminSession is implemented.
})
