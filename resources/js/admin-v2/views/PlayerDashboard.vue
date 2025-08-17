<script>

import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";
import TilesBlock from "@/components/TilesBlock.vue";
import CardWidget from "@/components/CardWidget.vue";
import CategoryPoints from "@/views/dashboard/CategoryPoints.vue";

export default {
    name: "PlayerDashboard",
    components: {CategoryPoints, HeroBar, TitleBar, TilesBlock, CardWidget},
    inject: ["clubhouseId"],
    data() {
        return {
            titleStack: ['Spillerportal', 'Start'],
            activeTab: 0,
            features: [
                {
                    title: 'Automatisk kalender',
                    description: 'Få dine holdkampe direkte i Google Kalender, Outlook eller Apple Kalender',
                    icon: 'calendar-sync-outline',
                    available: true,
                    route: `/c-${this.clubhouseId}/player/calendar-generator`,
                    color: 'is-info'
                },
                {
                    title: 'Spillerstatistikker',
                    description: 'Se dine kampe, resultater og udvikling over tid',
                    icon: 'chart-line',
                    available: false,
                    color: 'is-info'
                }
            ]
        }
    },
    methods: {
        navigateToFeature(feature) {
            if (feature.available && feature.route) {
                this.$router.push(feature.route);
            }
        }
    }
}

</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <section class="section is-main-section">
            <!-- Welcome Section -->
            <div class="container">
                <div class="columns is-centered">
                    <div class="column is-8">
                        <div class="card">
                            <div class="card-content has-text-centered">
                                <div class="content">
                                    <h2 class="title is-3 has-text-info">
                                        <b-icon icon="badminton" size="is-large" class="mr-3"></b-icon>
                                        Din spillerportal
                                    </h2>
                                    <p class="subtitle is-5 has-text-grey-dark">
                                        Små værktøjer som gør livet som badmintonspiller nemmere
                                    </p>
                                    <p class="is-size-6 has-text-grey">
                                        Denne portal gør det nemmere for dig at holde styr på holdkampe.
                                        Vi starter med automatisk kalender-sync, så du aldrig går glip af en kamp igen.
                                        Flere smarte værktøjer kommer snart!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="columns is-multiline is-centered mt-5">
                    <div class="column is-12">
                        <h3 class="title is-4 has-text-centered mb-5">Tilgængelige værktøjer</h3>
                    </div>

                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="column is-6-tablet is-4-desktop"
                    >
                        <div
                            class="card feature-card"
                            :class="{ 'is-clickable': feature.available, 'is-disabled': !feature.available }"
                            @click="navigateToFeature(feature)"
                        >
                            <div class="card-content">
                                <div class="media">
                                    <div class="media-left">
                                        <figure class="image is-48x48">
                                            <b-icon
                                                :icon="feature.icon"
                                                size="is-large"
                                                :type="feature.color"
                                            ></b-icon>
                                        </figure>
                                    </div>
                                    <div class="media-content">
                                        <p class="title is-6">
                                            {{ feature.title }}
                                            <b-tag
                                                v-if="feature.available"
                                                type="is-info"
                                                size="is-small"
                                                class="ml-2"
                                            >
                                                Tilgængelig
                                            </b-tag>
                                            <b-tag
                                                v-else
                                                type="is-light"
                                                size="is-small"
                                                class="ml-2"
                                            >
                                                Kommer snart
                                            </b-tag>
                                        </p>
                                    </div>
                                </div>
                                <div class="content">
                                    <p class="is-size-7 has-text-grey-dark">
                                        {{ feature.description }}
                                    </p>
                                </div>
                            </div>

                            <footer class="card-footer" v-if="feature.available">
                                <div class="card-footer-item">
                                    <b-button
                                        type="is-info"
                                        size="is-small"
                                        icon-left="arrow-right"
                                        outlined
                                    >
                                        Åbn værktøj
                                    </b-button>
                                </div>
                            </footer>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.feature-card {
    height: 100%;
    transition: all 0.3s ease;
    border: 1px solid #e8e8e8;
}

.feature-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.feature-card.is-clickable {
    cursor: pointer;
}

.feature-card.is-clickable:hover {
    border-color: #3273dc;
}

.feature-card.is-disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.feature-card.is-disabled:hover {
    transform: none;
    box-shadow: none;
}

.main-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stats-section {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 2rem;
}

.notification.is-light {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
}
</style>
