<template>
    <fragment>
        <b-navbar class="container">
            <template slot="start">
                <b-navbar-item tag="router-link" :to="{ path: '/rounds-generator' }">
                    Kamp runder generator
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
