<template>
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
    <a-form layout="vertical">
        <pre>{{ formData }}</pre>
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <a-form-item
                    :label="$t('attendance.user')"
                    name="user_id"
                    :help="rules.user_id ? rules.user_id.message : null"
                    :validateStatus="rules.user_id ? 'error' : null"
                    class="required"
                >
                <span style="display: flex">
                    <a-select
                        v-model:value="formData.user_id"
                        :placeholder="
                            $t('common.select_default_text', [
                                $t('attendance.user'),
                            ])
                        "
                        :allowClear="true"
                        optionFilterProp="title"
                        show-search
                    >
                        <a-select-option
                            v-for="user in users"
                            :key="user.xid"
                            :value="user.xid"
                            :title="user.name"
                        >
                            <user-list-display
                                :user="user"
                                whereToShow="select"
                            />
                        </a-select-option>
                    </a-select>
                    <UserAddButton @onAddSuccess="userAdded" />
                </span>
                </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('attendance.date') + ' From'"
                        name="date"
                        :help="rules.date ? rules.date.message : null"
                        :validateStatus="rules.date ? 'error' : null"
                        class="required"
                    >
                        <a-date-picker
                            v-model:value="formData.date"
                            :format="appSetting.date_format"
                            valueFormat="YYYY-MM-DD"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>

            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                <a-form-item
                    :label="$t('attendance.date') + ' To'"
                    name="date"
                    :help="rules.date ? rules.date.message : null"
                    :validateStatus="rules.date_to ? 'error' : null"
                    class="required"
                >
                    <a-date-picker
                        v-model:value="formData.date_to"
                        :format="appSetting.date_format"
                        valueFormat="YYYY-MM-DD"
                        style="width: 100%"
                    />
                </a-form-item>
            </a-col>
        </a-row>

        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('shift.clock_in_time')"
                        name="time_in"
                        :help="rules.time_in ? rules.time_in.message : null"
                        :validateStatus="rules.time_in ? 'error' : null"
                        class="required"
                    >
                        <a-time-picker
                            v-model:value="formData.time_in"
                            valueFormat="HH:mm:ss"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>


                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('shift.clock_out_time')"
                        name="time_out"
                        :help="rules.time_out ? rules.time_out.message : null"
                        :validateStatus="rules.time_out ? 'error' : null"
                        class="required"
                    >
                        <a-time-picker
                            v-model:value="formData.time_out"
                            valueFormat="HH:mm:ss"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>

                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('user.schedule_type')"
                        name="schedule_type"
                        :help="rules.schedule_type ? rules.schedule_type.message : null"
                        :validateStatus="rules.schedule_type ? 'error' : null"
                        class="required"
                    >
                        <a-radio-group v-model:value="formData.schedule_type">
                            <a-radio :value="'RVR'">{{ $t('user.is_reliever') }}</a-radio>
                            <a-radio :value="'OVD'">{{ $t('user.override_schedule') }}</a-radio>
                            <a-radio :value="'shift'">Main Shift</a-radio>
                        </a-radio-group>
                    </a-form-item>
                </a-col>


                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item :label="$t('user.location_id')" required>
                        <a-select
                            v-model:value="formData.schedule_location_id"
                            :placeholder="$t('common.select_default_text', [$t('user.location_id')])"
                            :allowClear="true"
                            optionFilterProp="title"
                            show-search
                            style="width: 100%"
                        >
                            <a-select-option
                                v-for="location in locations"
                                :key="location.xid"
                                :value="location.xid"
                                :title="location.name"
                            >
                                {{ location.name }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
        </a-row>

        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <a-form-item
                    :label="'Rest Day'"
                    name="rest_day"
                    :help="rules.rest_day ? rules.rest_day.message : null"
                    :validateStatus="rules.rest_day ? 'error' : null"
                    class="required"
                >
                <span style="display: flex">
                    <a-select
                        v-model:value="formData.rest_day"
                        :placeholder="
                            $t('common.select_default_text', [
                                'Rest Day',
                            ])
                        "
                        :allowClear="true"
                        optionFilterProp="title"
                        show-search
                    >
                        <a-select-option
                            v-for="day in days"
                            :key="day.key"
                            :value="day.key"
                            :title="day.label"
                        >
                            {{ day.label }}
                        </a-select-option>
                    </a-select>
                </span>
                </a-form-item>
            </a-col>
        </a-row>
    </a-form>
    <template #footer>
        <a-space>
            <a-button
                key="submit"
                type="primary"
                :loading="loading"
                @click="onSubmit"
            >
                <template #icon>
                    <SaveOutlined />
                </template>
                {{ addEditType == "add" ? $t("common.create") : $t("common.update") }}
            </a-button>
            <a-button key="back" @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </a-space>
    </template>
    </a-drawer>
    
</template>


<script>
import { LoadingOutlined, PlusOutlined, SaveOutlined } from "@ant-design/icons-vue";
import { defineComponent, onMounted, ref } from "vue";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";
import UserListDisplay from "../../../../common/components/user/UserListDisplay.vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";
import hrmManagement from "../../../../common/composable/hrmManagement";
import LeaveTypeAddButton from "../../leave/leave-types/AddButton.vue";
import UserAddButton from "../../staff-members/users/StaffAddButton.vue";


export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        UserAddButton,
        DateTimePicker,
        LeaveTypeAddButton,
        hrmManagement,
        UserListDisplay,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();

        const { appSetting, disabledDate, permsArray } = common();
        const users = ref([]);
        const locations = ref([]);
        const userUrl = "users?limit=10000";
        const locationUrl = "locations?limit=10000";
        const { formatShiftTime24Hours, formatShiftTime12Hours } = hrmManagement();

        const onSubmit = () => {

            console.log(props.formData)
            addEditRequestAdmin({
                url: props.url,
                data: {
                    ...props.formData,
                },

                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };


        onMounted(() => {
            const usersPromise = axiosAdmin.get(userUrl);
            const locationPromise = axiosAdmin.get(locationUrl);
            Promise.all([usersPromise, locationPromise]).then(
                ([userResponse, locationResponse,]) => {
                    users.value = userResponse.data;
                    locations.value = locationResponse.data;
                }
            );
        });

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        const userAdded = () => {
            axiosAdmin.get(userUrl).then((response) => {
                users.value = response.data;
                emit("addListSuccess", { type: "user_id", id: xid });
            });
        };


        const days = [
            { key: 'M', label: 'Monday' },
            { key: 'T', label: 'Tuesday' },
            { key: 'W', label: 'Wednesday' },
            { key: 'TH', label: 'Thursday' },
            { key: 'F', label: 'Friday' },
            { key: 'SA', label: 'Saturday' },
            { key: 'SU', label: 'Sunday' }
        ];


        return {
            loading,
            rules,
            onClose,
            onSubmit,
            appSetting,
            disabledDate,
            permsArray,
            users,
            userAdded,
            locations,
            days,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },

});
</script>