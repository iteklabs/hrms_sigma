<template>
    <a-card class="attendance-card">
        <div class="attendance-time">
            <h3>{{ $t("menu.attendances") }}</h3>
            <p>{{ attendanceTime }}</p>
        </div>

        <div class="progress-section">
            <a-progress
                type="circle"
                :percent="percent"
                :format="() => totalHours"
                :strokeColor="progressColor"
            />
        </div>

        <div class="details">
            <a-tag :color="tagColor">
                {{ $t(`attendance.production`) }}: {{ productionHours }}</a-tag
            >
            <p>
                <ClockCircleOutlined />
                {{ $t(`attendance.clock_in_date_time`) }}
                {{ punchInTime }}
            </p>
        </div>
        <a-row v-if="isSelfClocking" class="mt-30" :style="{ textAlign: 'center' }">
            <a-col :span="24">
                <a-space>
                    <a-button
                        v-if="showClockInButton"
                        type="primary"
                        @click="markAttendance('clock-in')"
                        :loading="clockInLoading"
                    >
                        <ClockCircleOutlined />
                        {{ $t("hrm_dashboard.clock_in") }}
                    </a-button>
                    <a-button v-else type="primary" :disabled="true">
                        {{ $t("hrm_dashboard.clocked_in") }} :
                        {{ formatTime(clockInDateTime) }}
                    </a-button>

                    <a-button
                        v-if="showClockOutButton"
                        type="primary"
                        @click="markAttendance('clock-out')"
                        :loading="clockOutLoading"
                    >
                        <LogoutOutlined />
                        {{ $t("hrm_dashboard.clock_out") }}
                    </a-button>
                    <a-button v-else type="primary" :disabled="true">
                        {{ $t("hrm_dashboard.clocked_out") }} :
                        {{ formatTime(clockOutDateTime) }}
                    </a-button>
                </a-space>
            </a-col>
        </a-row>
    </a-card>
</template>

<script>
import {
    ref,
    watchEffect,
    defineComponent,
    watch,
    onUnmounted,
    computed,
    onMounted,
} from "vue";
import { ClockCircleOutlined, LogoutOutlined } from "@ant-design/icons-vue";
import { theme } from "ant-design-vue";
import common from "@/common/composable/common";

import { useHrmStore } from "../../../../main/store/hrmStore";

export default defineComponent({
    props: ["data"],
    components: {
        ClockCircleOutlined,
        LogoutOutlined,
    },
    setup(props) {
        const { token } = theme.useToken();
        const { dayjs, formatTime } = common();
        const hrmStore = useHrmStore();
        const attendanceTime = ref("");
        const totalHours = ref("");
        const productionHours = ref("");
        const punchInTime = ref("");

        const progressColor = ref("#52c41a");
        const tagColor = ref("black");
        const clockInLoading = ref(false);
        const clockOutLoading = ref(false);
        const ipAddress = ref("");
        const isSelfClocking = computed(() => hrmStore.isSelfClocking);
        const attendanceData = ref([]);
        const showClockInButton = computed(() => hrmStore.showClockInButton);
        const showClockOutButton = computed(() => hrmStore.showClockOutButton);
        const clockInDateTime = computed(() => hrmStore.clockInDateTime);
        const clockOutDateTime = computed(() => hrmStore.clockOutDateTime);
        const percent = ref(0);
        let intervalId = null;

        onMounted(() => {
            updateAttendanceTime();
            intervalId = setInterval(updateAttendanceTime, 1000);
            axiosAdmin.get("/hrm/today-attendance-details").then((response) => {
                attendanceData.value = response.data;
                ipAddress.value = response.data.ip_address;

                updateHrmStoreData(response);
            });
        });

        const markAttendance = (attendanceType) => {
            if (attendanceType == "clock-in") {
                clockInLoading.value = true;
            } else if (attendanceType == "clock-out") {
                clockOutLoading.value = true;
            }
            axiosAdmin
                .post("/hrm/mark-attendance", { type: attendanceType })
                .then((response) => {
                    ipAddress.value = response.data.ip_address;
                    attendanceData.value = response.data;

                    if (attendanceType == "clock-in") {
                        clockInLoading.value = false;
                    } else if (attendanceType == "clock-out") {
                        clockOutLoading.value = false;
                    }

                    updateHrmStoreData(response);
                });
        };

        const updateHrmStoreData = (response) => {
            hrmStore.updateSelfClocking(response.data.self_clocking);
            hrmStore.updateShowClockInButton(response.data.show_clock_in_button);
            hrmStore.updateShowClockOutButton(response.data.show_clock_out_button);
            hrmStore.updateClockInDateTime(response.data.clock_in_date_time);
            hrmStore.updateClockOutDateTime(response.data.clock_out_date_time);
        };

        // Apply theme changes dynamically
        watchEffect(() => {
            document.documentElement.style.setProperty("--text-color", token.colorText);
            document.documentElement.style.setProperty(
                "--secondary-text-color",
                token.colorTextSecondary
            );
            document.documentElement.style.setProperty(
                "--card-background",
                token.colorBgContainer
            );
            document.documentElement.style.setProperty(
                "--border-color",
                token.colorBorder
            );

            progressColor.value = token.colorSuccess;
            tagColor.value = token.colorPrimary;
        });

        const updateAttendanceTime = () => {
            const now = dayjs();
            attendanceTime.value = now.format("hh:mm A, DD MMM YYYY");
        };

        onUnmounted(() => {
            if (intervalId) clearInterval(intervalId);
        });

        watch(
            props,
            (newVal) => {
                const attendance = newVal?.data?.attendances?.[0];

                if (attendance && attendance.clock_in_time) {
                    const clockInTime = dayjs(
                        `${attendance.date} ${attendance.clock_in_time}`,
                        "YYYY-MM-DD HH:mm:ss"
                    );

                    const updateProductionTime = () => {
                        const now = dayjs();
                        let workDayMinutes = 8 * 60;

                        const endTime = attendance.clock_out_time
                            ? dayjs(attendance.clock_out_time, "HH:mm:ss")
                            : now;

                        const diffMinutes = endTime.diff(clockInTime, "minute");

                        let formattedTime;
                        if (diffMinutes < 60) {
                            formattedTime = `${diffMinutes} min`;
                        } else {
                            const hours = Math.floor(diffMinutes / 60);
                            const mins = diffMinutes % 60;
                            formattedTime =
                                mins > 0 ? `${hours} hr ${mins} min` : `${hours} hr`;
                        }

                        punchInTime.value = clockInTime.format("hh:mm A");
                        productionHours.value = formattedTime;
                        totalHours.value = formattedTime;

                        // Calculate progress percentage
                        percent.value = Math.min(
                            Math.round((diffMinutes / workDayMinutes) * 100),
                            100
                        );
                    };

                    updateProductionTime();
                    const interval = setInterval(updateProductionTime, 1000);
                    onUnmounted(() => clearInterval(interval));
                } else {
                    attendanceTime.value = "N/A";
                    punchInTime.value = "N/A";
                    productionHours.value = "N/A";
                }
            },
            { immediate: true }
        );

        return {
            attendanceTime,
            totalHours,
            productionHours,
            punchInTime,
            progressColor,
            tagColor,
            markAttendance,
            clockInLoading,
            clockOutLoading,
            formatTime,
            isSelfClocking,
            percent,
            showClockInButton,
            showClockOutButton,
            clockInDateTime,
            clockOutDateTime,
        };
    },
});
</script>

<style scoped>
.attendance-card {
    width: 100%;
    text-align: center;
    padding: 9px;
    color: var(--text-color);
    height: 96.4%;
}

.attendance-time h3 {
    margin-bottom: 4px;
    font-weight: 600;
    color: var(--text-color);
}

.attendance-time p {
    color: var(--secondary-text-color);
}

.progress-section {
    margin: 16px 0;
}

.details {
    margin-bottom: 16px;
}

.details p {
    color: var(--secondary-text-color);
    font-size: 14px;
    margin: 8px 0 0;
    margin-bottom: -20px;
}
.ant-result,
.ant-result-icon {
    margin-bottom: -33px;
}
</style>
