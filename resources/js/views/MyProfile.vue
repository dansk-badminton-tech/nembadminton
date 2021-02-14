<template>
    <fragment>
        <ChangeProfile/>
        <ChangePassword/>
    </fragment>
</template>

<script>
import ChangePassword from "./ChangePassword";
import ChangeProfile from "./ChangeProfile";

export default {
    name: "MyProfile",
    components: {ChangeProfile, ChangePassword}
}
</script>

