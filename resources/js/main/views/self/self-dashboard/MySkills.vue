<template>
    <a-card
        class="skills-container"
        title="My Skills"
        :bodyStyle="{
            padding: '11px',
        }"
    >
        <template #extra>
            <a-button type="text">
                <template #icon>
                    <CalendarOutlined />
                </template>
                2024
            </a-button>
        </template>
        <a-list
            :data-source="skills"
            item-layout="horizontal"
            class="skills-list"
        >
            <template #renderItem="{ item }">
                <a-list-item class="skill-item">
                    <a-list-item-meta :description="`Updated: ${item.updated}`">
                        <template #title>
                            <span class="skill-name">{{ item.name }}</span>
                        </template>
                    </a-list-item-meta>
                    <a-progress
                        type="circle"
                        :percent="item.percentage"
                        :stroke-color="item.color"
                        :width="60"
                        :size="73"
                    />
                </a-list-item>
            </template>
        </a-list>
    </a-card>
</template>

<script setup>
import { ref } from "vue";
import { CalendarOutlined } from "@ant-design/icons-vue";

const skills = ref([
    { name: "Figma", updated: "15 May 2025", percentage: 95, color: "#fa541c" },
    { name: "HTML", updated: "12 May 2025", percentage: 85, color: "#52c41a" },
    { name: "CSS", updated: "12 May 2025", percentage: 70, color: "#722ed1" },
    {
        name: "Wordpress",
        updated: "15 May 2025",
        percentage: 61,
        color: "#1890ff",
    },
    {
        name: "Javascript",
        updated: "13 May 2025",
        percentage: 58,
        color: "#000",
    },
]);
</script>

<style scoped>
.skills-container {
    width: 100%;
}
.skills-list {
    padding: 0;
}
.skill-item {
    display: flex;
    justify-content: space-between;
    align-items: left;
    border-bottom: 1px solid #424242;
}

html.dark .skill-item {
    border-bottom: 1px solid #424242;
}

.skill-item:first-child {
    padding-top: 0;
}

.skill-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.skill-name {
    font-weight: bold;
}
.progressBarSize,
.ant-progress-inner {
    width: 63px !important;
    height: 63px !important;
}
</style>
