<template>
    <App>
        <div class="insights-page-container">
            <div class="insights-header">
                <h1>Phân tích trang</h1>
                <p class="page-name">{{ page.name }}</p>
            </div>

            <PageInsights
                :page="page"
                :insights="insights"
            />
        </div>
    </App>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import App from '../../Layouts/App.vue';
import PageInsights from '../../Components/Pages/PageInsights.vue';
import axios from 'axios';

const props = defineProps({
    page: {
        type: Object,
        required: true
    },
    insights: {
        type: Object,
        default: () => ({})
    }
});

const insights = ref(props.insights);

onMounted(() => {
    if (!insights.value || Object.keys(insights.value).length === 0) {
        loadInsights();
    }
});

const loadInsights = async () => {
    try {
        const response = await axios.get(`/pages/${props.page.id}/insights`);
        insights.value = response.data;
    } catch (error) {
        console.error('Error loading insights:', error);
    }
};
</script>

<style scoped>
.insights-page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.insights-header {
    margin-bottom: 24px;
    text-align: center;
}

.insights-header h1 {
    font-size: 32px;
    font-weight: 700;
    color: #050505;
    margin-bottom: 8px;
}

.page-name {
    font-size: 18px;
    color: #65676b;
}
</style>


