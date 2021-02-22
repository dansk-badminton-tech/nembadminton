<template>
    <div class="content has-text-grey has-text-centered">
        <p>
            <b-icon
                icon="user-alt"
                size="is-large">
            </b-icon>
        </p>
        <p v-if="!$apollo.loading && me.player_id !== null">Ingen holdkampe hvor du er spiller, endnu</p>
        <p v-if="!$apollo.loading && me.player_id === null">For at kunne se dine holdkampe hvor du er spiller skal du tilføje dit Badminton ID til din profil</p>
        <b-button v-if="!$apollo.loading && me.player_id === null"
                  tag="router-link"
                  to="/my-profile"
                  type="is-primary">
            Gå til min profil
        </b-button>
    </div>
</template>
<script>
import gql from "graphql-tag"

export default {
    name: 'UpdateYourProfileAction',
    apollo: {
        me: {
            query: gql`
                query {
                    me{
                        id
                        player_id
                    }
                }
            `
        }
    },
}
</script>
