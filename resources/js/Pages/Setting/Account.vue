<template>
    <Index>
        <div class="content">
            <div class="settings-form p-4">
                <h2>Your Account</h2>
                <form action="" method="" class="mt-4 settings-form">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="settingsFirstName">First Name</label>
                            <input type="text" class="form-control" id="settingsFirstName" placeholder="First Name" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="settingsLastName">Last Name</label>
                            <input type="text" class="form-control" id="settingsLastName" placeholder="Last Name" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-row mb-3 align-items-center">
                            <div class="col">
                                <label for="settingsUsername">Username</label>
                                <input type="email" class="form-control" id="settingsUsername"
                                    aria-describedby="usernameHelp" value="arthur_minasyan_96" placeholder="Username" />
                                <small id="usernameHelp" class="form-text text-muted">Your public username is the same
                                    as your
                                    timeline address.</small>
                            </div>
                            <div class="col check-username">
                                <span><i class="bx bx-check success"></i>
                                    Username is available</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Index>
</template>

<script setup lang="ts">
import Index from "./Index.vue";
</script>
<style scoped>

@import '../../../css/forms.css';
@import '../../../css/settings.css';

</style>
