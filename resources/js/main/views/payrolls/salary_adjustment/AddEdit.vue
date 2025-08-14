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

        <a-form layout="vertical" id="add_edit_user_form">
            <a-tabs v-model:activeKey="addEditActiveTab">
                <a-tab-pane key="adjustment" :tab="$t('menu.salary_adjustment')">
                    
                    <a-row :gutter="16">
                        <!-- <pre>{{ formData }}</pre>  -->
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salary_adjustment.name')"
                                name="input_text"
                                class="required"
                            >
                                <a-input v-model:value="formData.name" />
                            </a-form-item>
                        </a-col>

                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salary_adjustment.process_type')"
                                name="process_type"
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.process_payment"
                                    style="width: 100%"
                                    :placeholder="$t('salary_adjustment.process_type')"
                                    @change="onProcessPaymentChange"
                                >
                                    <a-select-option
                                        value="recurring"
                                    >
                                        {{ $t('salary_adjustment.recurring') }}
                                    </a-select-option>
                                    <a-select-option value="date_range">
                                        Specific Payroll Batch
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <!-- Recurring Options -->
                        <template v-if="formData.process_payment === 'recurring'">
                            <a-col :span="24">
                                <h3 style="margin-bottom: 10px;">Start Batch</h3>
                            </a-col>
                                <a-col :xs="24" :sm="24" :md="8" :lg="8">
                                    <a-form-item
                                        :label="'Year'"
                                        name="start_year_specific"
                                    >
                                        <a-date-picker
                                            v-model:value="formData.start_year_specific"
                                            style="width: 100%"
                                            picker="year"
                                            :format="'YYYY'"
                                            :placeholder="'Select Year'"
                                            :disabled-date="disabledYear"
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                    <a-form-item
                                        :label="'Month'"
                                        name="start_month_specific"
                                    >
                                        <a-date-picker
                                            v-model:value="formData.start_month_specific"
                                            style="width: 100%"
                                            picker="month"
                                            :format="'MMMM'"
                                            :placeholder="'Select Month'"
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                    <a-form-item
                                        :label="'Cut-Off Type'"
                                        name="start_cut_off_specific"
                                    >
                                        <a-select
                                            v-model:value="formData.start_cut_off_specific"
                                            style="width: 100%"
                                            :placeholder="'Select Cut-off'"
                                        >
                                            <a-select-option value="A">A</a-select-option>
                                            <a-select-option value="B">B</a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>
                                <a-col :span="24" style="margin-top: 20px;">
                                    <h3 style="margin-bottom: 10px;">End Batch</h3>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="8" :lg="8">
                                    <a-form-item
                                        :label="'Year'"
                                        name="end_year_specific"
                                    >
                                        <a-date-picker
                                            v-model:value="formData.end_year_specific"
                                            style="width: 100%"
                                            picker="year"
                                            :format="'YYYY'"
                                            :placeholder="'Select Year'"
                                            :disabled-date="disabledYear"
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                    <a-form-item
                                        :label="'Month'"
                                        name="end_month_specific"
                                    >
                                        <a-date-picker
                                            v-model:value="formData.end_month_specific"
                                            style="width: 100%"
                                            picker="month"
                                            :format="'MMMM'"
                                            :placeholder="'Select Month'"
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                    <a-form-item
                                        :label="'Cut-Off Type'"
                                        name="end_cut_off_specific"
                                    >
                                        <a-select
                                            v-model:value="formData.end_cut_off_specific"
                                            style="width: 100%"
                                            :placeholder="'Select Cut-off'"
                                        >
                                            <a-select-option value="A">A</a-select-option>
                                            <a-select-option value="B">B</a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>
                            
                        </template>


                        <!-- Specific payroll batch -->
                        <template v-if="formData.process_payment === 'date_range'">
                         
                                <!-- Date From -->
                                <a-col :xs="24" :sm="24" :md="8" :lg="8">
                                    <a-form-item
                                        :label="'Year'"
                                        name="year_specific"
                                    >
                                        <a-date-picker
                                            v-model:value="formData.year_specific"
                                            style="width: 100%"
                                            picker="year"
                                            :format="'YYYY'"
                                            :placeholder="'Select Year'"
                                            :disabled-date="disabledYear"
                                        />
                                    </a-form-item>
                                </a-col>


                                <!-- Date To -->
                                <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                    <a-form-item
                                        :label="'Month'"
                                        name="month_specific"
                                    >
                                        <a-date-picker
                                            v-model:value="formData.month_specific"
                                            style="width: 100%"
                                            picker="month"
                                            :format="'MMMM'"
                                            :placeholder="'Select Month'"
                                        />
                                    </a-form-item>
                                </a-col>

                                <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                    <a-form-item
                                        :label="'Cutt-Off Type'"
                                        name="cut_off_specific"
                                    >
                                        <a-select
                                            v-model:value="formData.cut_off_specific"
                                            style="width: 100%"
                                            :placeholder="'Select Cut-off'"
                                        >
                                            <a-select-option value="A">A</a-select-option>
                                            <a-select-option value="B">B</a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>


                    
                        </template>
                        


                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salary_adjustment.amount')"
                                name="amount"
                                class="required"
                            >
                                <a-input-number
                                    v-model:value="formData.amount"
                                    style="width: 100%"
                                    :min="0"
                                    :placeholder="$t('salary_adjustment.amount')"
                                />
                            </a-form-item>
                        </a-col>


                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salary_adjustment.type_taxable')"
                                name="type_taxable"
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.type"
                                    style="width: 100%"
                                    :placeholder="$t('salary_adjustment.type_taxable')"
                                >
                                    <a-select-option value="NT">{{ $t('salary_adjustment.non_taxable') }}</a-select-option>
                                    <a-select-option value="T">{{ $t('salary_adjustment.taxable') }}</a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>

                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
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
                                :label="$t('salary_adjustment.adjustment_type')"
                                name="adjustment_type"
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.adjustment_type"
                                    style="width: 100%"
                                    :placeholder="$t('salary_adjustment.adjustment_type')"
                                >
                                    <a-select-option value="basic_pay">Basic</a-select-option>
                                    <a-select-option value="alowance_pay">Allowance</a-select-option>
                                    <a-select-option value="overtime_pay">Overtime</a-select-option>
                                    <a-select-option value="SPNWD_pay">Special non-working day</a-select-option>
                                    <a-select-option value="rest_day_pay">Rest Day</a-select-option>
                                    <a-select-option value="holiday_pay">Holiday</a-select-option>
                                </a-select>
                            </a-form-item>
                    </a-col>
                        
                    </a-row>
                    
                </a-tab-pane>
                
            </a-tabs>
        </a-form>
        <template #footer>
            <a-space>
                <a-button type="primary" @click="onSubmit" :loading="loading">
                    <template #icon> <SaveOutlined /> </template>
                    {{ addEditType == "add" ? $t("common.create") : $t("common.update") }}
                </a-button>
                <a-button @click="onClose">
                    {{ $t("common.cancel") }}
                </a-button>
            </a-space>
        </template>
    </a-drawer>
</template>

<script>
import { LoadingOutlined, PlusOutlined, SaveOutlined } from "@ant-design/icons-vue";
import { defineComponent, onMounted, reactive, ref, watch } from "vue";
import FormItemHeading from "../../../../common/components/common/typography/FormItemHeading.vue";
import UserListDisplay from "../../../../common/components/user/UserListDisplay.vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";
import { useAuthStore } from "../../../../main/store/authStore";
import UserAddButton from "../../staff-members/users/StaffAddButton.vue";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "reFetchListOfData",
        "successMessage",
        'record'
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        FormItemHeading,
        UserAddButton,
        UserListDisplay
    },
    
    setup(props, { emit }) {
        const { permsArray, user, appSetting, dayjs } = common();
        const { addEditRequestAdmin, loading, rules, addEditActiveTab } = apiAdmin(
            "adjustment"
        );
        const roles = ref([]);
        const authStore = useAuthStore();
        const departments = ref([]);
        const designations = ref([]);
        const selectedVisibility = ref("manager");
        const locations = ref([]);
        const shifts = ref([]);
        const joiningDate = ref("");
        const showVisibilty = ref(false);
        const adjustedVisible = ref(false);
        const newData = ref({});
        const users = ref([]);
        const userUrl = "users?limit=10000";
        const formData = reactive({
            start_month_specific: null, // Day.js object for picker
            start_month_string: ""      // Always plain "YYYY-MM" for API
        });
        

        watch(
            () => formData.start_month_specific,
            (newVal) => {
                formData.start_month_string = newVal
                ? newVal.format("YYYY-MM")
                : "";
            }
        );

        const onSubmit = () => {
            const processedFormData = {
                ...props.formData,
                start_year_specific: new Date(props.formData.start_year_specific).getFullYear(),
                end_year_specific: new Date(props.formData.end_year_specific).getFullYear(),
                start_month_specific: String(new Date(props.formData.start_month_specific).getMonth() + 1).padStart(2, '0'),
                end_month_specific: String(new Date(props.formData.end_month_specific).getMonth() + 1).padStart(2, '0'),
                month_specific: String(new Date(props.formData.month_specific).getMonth() + 1).padStart(2, '0'),
                year_specific: new Date(props.formData.year_specific).getFullYear(),
            };
            var newFormData = {
                ...processedFormData,
                visibility: selectedVisibility.value,
                ...newData.value,
            };

            console.log(newFormData)
            addEditRequestAdmin({
                id: newFormData.id,
                url: props.url,
                data: newFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                    authStore.updateAppAction();

                    if (user.value.xid == res.xid) {
                        authStore.updateUserAction();
                    }
                },
            });
        };
        
        
        onMounted(() => {

            // console.log('data',props.formData)
            const usersPromise = axiosAdmin.get(userUrl);

            Promise.all([usersPromise]).then(
                ([userResponse]) => {
                    users.value = userResponse.data;
                }
            );
            // console.log(props.formData)
            if (props.formData.start_year_specific) {
                props.formData.start_year_specific = dayjs(props.formData.start_year_specific, "YYYY");
            }
            if (props.formData.end_year_specific) {
                props.formData.end_year_specific = dayjs(props.formData.end_year_specific, "YYYY");
            }
            if (props.formData.start_month_specific) {
                props.formData.start_month_specific = dayjs(props.formData.start_month_specific, "MM");
            }
            if (props.formData.end_month_specific) {
                props.formData.end_month_specific = dayjs(props.formData.end_month_specific, "MM");
            }
            if (props.formData.year_specific) {
                props.formData.year_specific = dayjs(props.formData.year_specific, "YYYY");
            }
            if (props.formData.month_specific) {
                props.formData.month_specific = dayjs(props.formData.month_specific, "MM");
            }
        });


        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

    
        const disabledYear = (current) => {
            const currentYear = dayjs().year()
            return (
                current.year() < currentYear ||
                current.year() > currentYear + 10
            )
        }

        const onProcessPaymentChange = () => {
            props.formData.start_year_specific = null
            props.formData.end_year_specific = null
            props.formData.start_month_specific = null
            props.formData.end_month_specific = null
            props.formData.month_specific = null
            props.formData.year_specific = null
        }


        // console.log('data',props.formData)

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            roles,
            users,
            permsArray,
            appSetting,
            departments,
            designations,
            shifts,
            locations,
            joiningDate,
            showVisibilty,
            selectedVisibility,
            addEditActiveTab,
            adjustedVisible,
            onProcessPaymentChange,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "65%",
            disabledYear
        };
    },
});
</script>
