<script setup>

</script>

<template>
    <!-- Begin Contact Content -->
    <div class="section-light contact" id="contact">
        <div class="container">
            <div
                class="columns is-multiline"
                data-aos="fade-in-up"
                data-aos-easing="linear"
            >
                <div class="column is-12 about-me">
                    <h1 class="title has-text-centered section-title">
                        Kontakt - Feedback, features, Ris eller Ros.
                    </h1>
                </div>
                <div class="column is-8 is-offset-2">
                    <form
                        action="https://formspree.io/email@example.com"
                        method="POST"
                    >
                        <div class="field">
                            <label class="label">Name</label>
                            <div class="control has-icons-left">
                                <input
                                    class="input"
                                    type="text"
                                    placeholder="Ex. Jane Smith"
                                    name="Name"
                                />
                                <span class="icon is-small is-left">
                      <i class="fas fa-user"></i>
                    </span>
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Email</label>
                            <div class="control has-icons-left">
                                <input
                                    class="input"
                                    type="email"
                                    placeholder="Ex. hello@arctheme.com"
                                    name="Email"
                                />
                                <span class="icon is-small is-left">
                      <i class="fas fa-envelope"></i>
                    </span>
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Message</label>
                            <div class="control">
                    <textarea
                        class="textarea"
                        placeholder="Textarea"
                        name="Message"
                    ></textarea>
                            </div>
                        </div>
                        <div class="field">
                            <div class="control ">
                                <button class="button submit-button">
                                    Submit&nbsp;&nbsp;
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Contact Content -->
</template>

<style scoped>

</style>
