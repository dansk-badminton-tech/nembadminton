<script>
import ME from "../../../queries/me.gql";

export default {
    name: "CancellationRedirect",
    apollo: {
        me: {
            query: ME,
            result({data}) {
                if (data.me.cancellationCollector === null) {
                    this.$router.replace({name: "cancellation-landing"})
                } else {
                    this.$router.replace({name: "cancellation-view", params: {collectorId: data.me.cancellationCollector.id}});
                }
            }
        }
    }
}
</script>

<template>
    <div>
        <h1 class="title is-3">vent...</h1>
    </div>
</template>
