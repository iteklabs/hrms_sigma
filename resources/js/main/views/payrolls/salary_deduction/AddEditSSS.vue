<template>
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        :DataNeed="DataNeed"
        @close="onClose"
    >

    <a-form layout="vertical">
        <!-- <pre>{{ formData }}</pre> -->
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
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
            <a-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <a-form-item
                    :label="'Location'"
                    name="location_id"
                    :help="rules.location_id ? rules.location_id.message : null"
                    :validateStatus="rules.location_id ? 'error' : null"
                    class="required"
                >
                <span style="display: flex">
                    <a-select
                        v-model:value="formData.location_id"
                        :placeholder="
                            $t('common.select_default_text', [
                                'Location',
                            ])
                        "
                        :allowClear="true"
                        optionFilterProp="title"
                        show-search
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
                </span>
                </a-form-item>
            </a-col>
            <a-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <a-form-item
                    :label="'Type of Loan'"
                    name="type_of_loan"
                    :help="rules.type_of_loan ? rules.type_of_loan.message : null"
                    :validateStatus="rules.type_of_loan ? 'error' : null"
                    class="required"
                >
                <span style="display: flex">
                    <a-select
                        v-model:value="formData.type_of_loan"
                        :placeholder="
                            $t('common.select_default_text', [
                                'Type of Loan',
                            ])
                        "
                        :allowClear="true"
                        optionFilterProp="title"
                        show-search
                    >
                        <a-select-option
                            v-for="typeLoan in loan_type"
                            :key="typeLoan.value"
                            :value="typeLoan.value"
                            :title="typeLoan.label"
                        >
                            {{ typeLoan.label }}
                        </a-select-option>
                    </a-select>
                </span>
                </a-form-item>
            </a-col>
        </a-row>
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="8" :lg="8">
                <a-form-item
                    :label="$t('salary_adjustment.amount')"
                    name="total_amount_loan"
                    class="required"
                >
                    <a-input-number
                        v-model:value="formData.total_amount_loan"
                        style="width: 100%"
                        :min="0"
                        :placeholder="$t('salary_adjustment.total_amount_loan')"
                    />
                </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="24" :md="8" :lg="8">
                <a-form-item
                    :label="'No of Deductions'"
                    name="no_deductions"
                    class="required"
                >
                    <a-input-number
                        v-model:value="formData.no_deductions"
                        style="width: 100%"
                        :min="0"
                        :placeholder="'No of Deductions'"
                        :onChange="ComputeMonthlyDeduction()"
                    />
                </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="24" :md="4" :lg="4">
                <a-form-item
                    :label="'Type of Deduction'"
                    name="type_of_deduction"
                    class="required"
                >
                    <span style="display: flex">
                        <a-select
                            v-model:value="formData.type_of_deduction"
                            :placeholder="
                                $t('common.select_default_text', [
                                    'Type of Deduction',
                                ])
                            "
                            :allowClear="true"
                            optionFilterProp="title"
                            show-search
                        >
                            <a-select-option
                                :value="'s_monthly'"
                                :title="'Semi-Monthly'"
                            >
                                Semi-Monthly
                            </a-select-option>
                            <a-select-option
                                :value="'monthly'"
                                :title="'Monthly'"
                            >
                                Monthly
                            </a-select-option>
                        </a-select>
                    </span>
                </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="24" :md="4" :lg="4">
                <a-form-item
                    :label="'Schedule to Deduct'"
                    name="sched_of_deduction"
                    class="required"
                >
                    <span style="display: flex">
                        <a-select
                            v-model:value="formData.sched_of_deduction"
                            :placeholder="
                                $t('common.select_default_text', [
                                    'Schedule of Deduction',
                                ])
                            "
                            :allowClear="true"
                            optionFilterProp="title"
                            show-search
                        >
                            <a-select-option
                                :value="'A'"
                                :title="'1st Cut-Off'"
                            >
                                1st Cut-Off
                            </a-select-option>
                            <a-select-option
                                :value="'B'"
                                :title="'2nd Cut-Off'"
                            >
                                2nd Cut-Off
                            </a-select-option>
                        </a-select>
                    </span>
                </a-form-item>
            </a-col>

            
        </a-row>
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="4" :lg="4">
                <a-form-item
                    :label="'Monthly Deduction'"
                    name="amount_per_payroll"
                    class="required"
                >
                    <a-input-number
                        v-model:value="formData.amount_per_payroll"
                        style="width: 100%"
                        :min="0"
                        :placeholder="'Monthly Deduction'"
                        :disabled="false"
                        :readOnly="true"
                    />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :sm="24" :md="4" :lg="4">
                <a-form-item
                    :label="'Payroll Deduction'"
                    name="payroll_deduction"
                    class="required"
                >
                    <a-input-number
                        v-model:value="formData.payroll_deduction"
                        style="width: 100%"
                        :min="0"
                        :placeholder="'Payroll Deduction'"
                        :disabled="false"
                        :readOnly="true"
                    />
                </a-form-item>
            </a-col>
            


            <a-col :xs="24" :sm="24" :md="2" :lg="2">
                <a-form-item
                    :label="'Start Year'"
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

            <a-col :xs="24" :sm="12" :md="2" :lg="2">
                <a-form-item
                    :label="'Start Month'"
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

            <a-col :xs="24" :sm="12" :md="2" :lg="2">
                <a-form-item
                    :label="'Start Batch'"
                    name="start_batch_specific"
                >
                    <span style="display: flex">
                        <a-select
                            v-model:value="formData.start_batch_specific"
                            :placeholder="
                                $t('common.select_default_text', [
                                    'Schedule of Deduction',
                                ])
                            "
                            :allowClear="true"
                            optionFilterProp="title"
                            show-search
                        >
                            <a-select-option
                                :value="'A'"
                                :title="'A'"
                            >
                                A
                            </a-select-option>
                            <a-select-option
                                :value="'B'"
                                :title="'B'"
                            >
                                B
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
import { SaveOutlined } from "@ant-design/icons-vue";
import { computed, defineComponent, onMounted, ref, watch } from "vue";
import UserListDisplay from "../../../../common/components/user/UserListDisplay.vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";
import hrmManagement from "../../../../common/composable/hrmManagement";
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
        "DataNeed"
    ],
    components: {
        UserAddButton,
        hrmManagement,
        UserListDisplay,
        SaveOutlined
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { appSetting, disabledDate, permsArray } = common();
        const locations = ref([]);
        const userUrl = "users?limit=10000";
        const locationUrl = "locations?limit=10000";
        const users = ref([]);
        const loan_type = ref([]);
        const dataNeed = computed(() => props.DataNeed)
        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        onMounted(() => {
            const usersPromise = axiosAdmin.get(userUrl);
            const locationPromise = axiosAdmin.get(locationUrl);
            Promise.all([usersPromise, locationPromise]).then(
                ([userResponse, locationResponse]) => {
                    users.value = userResponse.data;
                    locations.value = locationResponse.data;

                }
            );

            
        });


        const userAdded = () => {
            axiosAdmin.get(userUrl).then((response) => {
                users.value = response.data;
                emit("addListSuccess", { type: "user_id", id: xid });
            });
        };

        // watch(() => props.DataNeed, (isOpen) => {
        //     props.formData.loan_name = isOpen;
        //     console.log('DataNeed changed to:', isOpen);
        //     if(isOpen === 'GovLoan') {
        //             // props.pageTitle = "Add/Edit Government Loan";

                    
        //             loan_type.value = [
        //                 { value: "SSSsalary", label: "SSS Salary Loan" },
        //                 { value: "SSScalamity", label: "SSS Calamity Loan" },
        //                 { value: "PAGIBIGsalary", label: "Pag-IBIG Salary Loan" },
        //                 { value: "PAGIBIGcalamity", label: "Pag-IBIG Calamity Loan" },
        //             ];
        //         } else if(isOpen === 'CompLoan') {
        //             loan_type.value = [
        //                 { value: "SELA's / COOP", label: "SECOOP" },
        //                 { value: "globe", label: "Globe" },
        //                 { value: "hmo", label: "HMO" },
        //                 { value: "aroe", label: "AROE" },
        //             ];
        //         }
        // });

        watch(() => props.visible, (isOpen) => {
            if (isOpen) {
                // console.log('Drawer opened, DataNeed =', props.addEditType)
                
                if (props.addEditType === "add") {
                    props.pageTitle = "Add " + props.DataNeed;
                } else {
                    // rules.value = props.rules || {};
                    props.DataNeed = props.formData.DataNeed;
                }
                // console.log('DataNeed:', props.DataNeed);
                // console.log('DataNeed:', props.formData.DataNeed);
                props.formData.loan_name = props.DataNeed;
                if(props.DataNeed == 'GovLoan' ||props.formData.DataNeed == 'GovLoan') {
                    loan_type.value = [
                        { value: "SSSsalary", label: "SSS Salary Loan" },
                        { value: "SSScalamity", label: "SSS Calamity Loan" },
                        { value: "PAGIBIGsalary", label: "Pag-IBIG Salary Loan" },
                        { value: "PAGIBIGcalamity", label: "Pag-IBIG Calamity Loan" },
                    ];
                } else if(props.DataNeed === 'CompLoan' ||props.formData.DataNeed == 'CompLoan') {
                    loan_type.value = [
                        { value: "SELA's / COOP", label: "SECOOP" },
                        { value: "globe", label: "Globe" },
                        { value: "hmo", label: "HMO" },
                        { value: "aroe", label: "AROE" },
                    ];
                }
            }
        })


        watch(() => props.addEditType, (type) => {
            console.log('addEditType changed to:', type);
            if (type === "add") {
                props.pageTitle = "Add " + props.DataNeed;
            } else {
                rules.value = props.rules || {};
            }
        });


        const ComputeMonthlyDeduction = () => {
            if (props.formData.no_deductions && props.formData.total_amount_loan) {
                props.formData.amount_per_payroll =
                    (props.formData.total_amount_loan / props.formData.no_deductions).toFixed(4);
            } else {
                props.formData.amount_per_payroll = 0;
            }
        };
        const onSubmit = () => {
            const newData = {
                ...props.formData,
                start_month_specific: props.formData.start_month_specific
                    ? props.formData.start_month_specific.format("MM")
                    : null,
                start_year_specific: props.formData.start_year_specific
                    ? props.formData.start_year_specific.format("YYYY")
                    : null,
            }

            addEditRequestAdmin({
                url: props.url,
                data: {
                    ...newData,
                },
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
            console.log(newData)
            console.log(props.addEditType)
            console.log(props.url)
            console.log(props.successMessage)
            console.log(props.pageTitle)
            
            

            // rules.value = {};
            // addEditRequestAdmin(
            //     props.url,
            //     props.addEditType,
            //     props.formData,
            //     props.successMessage,
            //     props.pageTitle
            // ).then((response) => {
            //     if (response.status === 200) {
            //         emit("onSuccess", response.data);
            //         onClose();
            //     } else {
            //         rules.value = response.data.errors || {};
            //     }
            // });
        };

        return {
            loading,
            rules,
            users,
            locations,
            loan_type,
            onClose,
            userAdded,
            ComputeMonthlyDeduction,
            onSubmit,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "90%",
        };
    }
});
</script>