<template>
    <a-card title="Projects" class="parent-card">
        <template #extra>
            <a-dropdown>
                <template #overlay>
                    <a-menu>
                        <a-menu-item key="1">Ongoing Projects</a-menu-item>
                        <a-menu-item key="2">Completed Projects</a-menu-item>
                    </a-menu>
                </template>
                <a-button type="text">Ongoing Projects ▼</a-button>
            </a-dropdown>
        </template>

        <a-row :gutter="[16, 16]">
            <a-col
                :xs="24"
                :sm="12"
                v-for="(project, index) in projects"
                :key="index"
            >
                <a-card class="project-card" hoverable>
                    <div class="project-header">
                        <strong>{{ project.title }}</strong>
                    </div>

                    <div class="project-info">
                        <a-avatar :src="project.leader.avatar" size="large" />
                        <div class="leader-info">
                            <strong>{{ project.leader.name }}</strong>
                            <span class="role">{{ project.leader.role }}</span>
                        </div>
                    </div>

                    <div class="deadline">
                        <CalendarOutlined class="icon" />
                        <div class="leader-info">
                            <span>{{ project.deadline }}</span>
                            <small>Deadline</small>
                        </div>
                    </div>

                    <div class="tasks">
                        <div class="tasks-left">
                            <CheckCircleOutlined class="icon green" />
                            <span
                                >Tasks: {{ project.completedTasks }}/{{
                                    project.totalTasks
                                }}</span
                            >
                        </div>
                        <div class="avatars">
                            <a-avatar-group>
                                <a-avatar
                                    v-for="(member, i) in project.teamMembers"
                                    :key="i"
                                    :src="member.avatar"
                                />
                            </a-avatar-group>
                            <a-tooltip title="More members">
                                <a-avatar style="background: #ff4d4f">{{
                                    project.extraMembers
                                }}</a-avatar>
                            </a-tooltip>
                        </div>
                    </div>

                    <div class="time-spent">
                        <span>Time Spent</span>
                        <span class="hours">
                            <strong>{{ project.spentHours }}</strong
                            >/<strong>{{ project.totalHours }}</strong> Hrs
                        </span>
                    </div>
                </a-card>
            </a-col>
        </a-row>
    </a-card>
</template>

<script setup>
import { ref } from "vue";
import {
    CalendarOutlined,
    CheckCircleOutlined,
    EllipsisOutlined,
} from "@ant-design/icons-vue";

const projects = ref([
    {
        title: "Office Management",
        leader: {
            name: "Anthony Lewis",
            role: "Project Leader",
            avatar: "https://i.pravatar.cc/50?img=5",
        },
        deadline: "14/01/2024",
        completedTasks: 6,
        totalTasks: 10,
        spentHours: 65,
        totalHours: 120,
        teamMembers: [
            { avatar: "https://i.pravatar.cc/50?img=6" },
            { avatar: "https://i.pravatar.cc/50?img=7" },
            { avatar: "https://i.pravatar.cc/50?img=8" },
        ],
        extraMembers: "+2",
    },
    {
        title: "Office Management",
        leader: {
            name: "Anthony Lewis",
            role: "Project Leader",
            avatar: "https://i.pravatar.cc/50?img=9",
        },
        deadline: "14/01/2024",
        completedTasks: 6,
        totalTasks: 10,
        spentHours: 65,
        totalHours: 120,
        teamMembers: [
            { avatar: "https://i.pravatar.cc/50?img=10" },
            { avatar: "https://i.pravatar.cc/50?img=11" },
            { avatar: "https://i.pravatar.cc/50?img=12" },
        ],
        extraMembers: "+2",
    },
]);
</script>

<style scoped>
.parent-card {
    width: 100%;
}

.project-card {
    border-radius: 4px;
    padding: 10px;
}

.project-header {
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 8px;
}

.project-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.leader-info {
    display: flex;
    flex-direction: column;
}

.role {
    font-size: 12px;
    color: gray;
}

.deadline,
.tasks,
.time-spent {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}
.tasks {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    border: 1px solid rgb(235, 232, 232);
    border-radius: 5px;
    padding: 5px;
    background: #f3f3f3;
}

.tasks-left {
    display: flex;
    align-items: center;
    gap: 6px; /* Ensures the icon and text stay close */
}

.icon {
    font-size: 28px;
    color: #ff4d4f;
}

.green {
    color: #52c41a;
}

.avatars {
    display: flex;
    align-items: center;
    gap: 4px;
}

.time-spent {
    justify-content: space-between;
    background: #f0f3f1;
    padding: 8px;
    border-radius: 6px;
}

.hours {
    font-weight: bold;
    color: #1890ff;
}
</style>
