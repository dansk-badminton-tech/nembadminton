<template>
    <div class="content has-text-grey has-text-centered">
        <p>
            <b-icon
                icon="user-alt"
                size="is-large">
            </b-icon>
        </p>
        <p>For at kunne se dine holdkampe hvor du er spiller skal du tilføje dit Badminton ID til din profil</p>
        <b-button
            tag="router-link"
            to="/my-profile"
            type="is-primary">
            Gå til min profil
        </b-button>
    </div>
</template>
<script>
export default {
    name: 'UpdateYourProfileAction'
}
</script>
