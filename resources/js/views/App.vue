<template>
    <fragment>
        <b-navbar class="container">
            <template slot="start">
                <!--                <b-navbar-item tag="router-link" :to="{ path: '/rounds-generator' }">-->
                <!--                    Kamp runder generator-->
                <!--                </b-navbar-item>-->
                <b-navbar-item :to="{ path: '/team-fight/recent' }" tag="router-link">
                    <b-icon icon="users"></b-icon>
                    <span class="nav-item-span">Holdkamp planner</span>
                </b-navbar-item>
            </template>
        </b-navbar>
        <section class="section">
            <div class="container">
                <router-view></router-view>
            </div>
        </section>
    </fragment>
</template>

<script>
export default {}
</script>

<style scoped>
.nav-item-span {
    margin-left: 0.5rem;
}
</style>
