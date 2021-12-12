<template>
    <div>
        <TopMenu/>
        <section class="section">
            <div class="container">
                <router-view></router-view>
            </div>
        </section>
    </div>
</template>

<script>
import TopMenu from "./TopMenu";

export default {
    name: "Base",
    components: {TopMenu}
}
</script>

