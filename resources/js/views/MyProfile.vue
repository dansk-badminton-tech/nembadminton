<template>
    <div>
        <ChangeProfile/>
        <ChangePassword/>
    </div>
</template>

<script>
import ChangePassword from "./ChangePassword";
import ChangeProfile from "./ChangeProfile";

export default {
    name: "MyProfile",
    components: {ChangeProfile, ChangePassword}
}
</script>

