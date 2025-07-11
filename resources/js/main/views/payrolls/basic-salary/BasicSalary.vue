<template>
    <a-form>
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <pre>{{ formData }}</pre>
                <a-form-item
                    :label="$t('salary_group.salary_group_id')"
                    name="salary_group_id"
                    :help="rules.salary_group_id ? rules.salary_group_id.message : null"
                    :validateStatus="rules.salary_group_id ? 'error' : null"
                >
                    <span style="display: flex">
                        <a-select
                            v-model:value="formData.salary_group_id"
                            style="width: 100%"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('salary_group.salary_group_id'),
                                ])
                            "
                            :allowClear="true"
                            @change="
                                fetchSalaryComponentsAndUsers(formData.salary_group_id)
                            "
                        >
                            <a-select-option
                                v-for="salaryGroup in salaryGroups"
                                :key="salaryGroup.xid"
                                :value="salaryGroup.xid"
                            >
                                {{ salaryGroup.name }}
                            </a-select-option>
                        </a-select>
                        <SalaryGroupAddButton @onAddSuccess="salaryGroupAdded" />
                    </span>
                </a-form-item>
            </a-col>
        </a-row>
        <a-row :gutter="16" v-if="inputVisible">
            <a-col :xs="24" :sm="24" :md="24" :lg="24"> <UserInfo :user="user" /></a-col>
        </a-row>
        <a-row 
        style="
            margin-top: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #d9d9d9;
            padding-bottom: 8px;
            display: flex;
            justify-content: space-between;
        "
        :gutter="16" class="new-class">
            <a-col :xs="24" :sm="24" :md="4" :lg="4">{{
                $t("basic_salary.salary_info")
            }}</a-col>
        </a-row>

        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="12">
                <a-form-item
                    :label="$t('basic_salary.monthly_amount')"
                    name="ctc_value"
                    :help="rules.ctc_value ? rules.ctc_value.message : null"
                    :validateStatus="rules.ctc_value ? 'error' : null"
                    class="required"
                >
                    <a-input-number
                        v-model:value="formData.basic_salary"
                        :placeholder="
                            $t('common.placeholder_default_text', [
                                $t('basic_salary.ctc_value'),
                            ])
                        "
                        min="0"
                        style="width: 70%"
                        @change="calculateSalary"
                    >
                        <template #addonBefore>
                            {{ appSetting.currency.symbol }}
                        </template>
                        <template #addonAfter>
                            <a-select
                                v-model:value="formData.calculation_type"
                                style="width: 120px"
                                @change="calculateSalary"
                            >
                                <a-select-option value="fixed">{{
                                    $t("basic_salary.fixed")
                                }}</a-select-option>

                                <a-select-option value="monthly">{{
                                    $t("basic_salary._Monthly")
                                }}</a-select-option>
                                <a-select-option value="s_monthly">{{
                                    $t("basic_salary._SMonthly")
                                }}</a-select-option>
                                <a-select-option value="daily">{{
                                    $t("basic_salary._Daily")
                                }}</a-select-option>
                                <a-select-option value="hourly">{{
                                    $t("basic_salary._Hourly")
                                }}</a-select-option>
                            </a-select>
                        </template>
                    </a-input-number>
                </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="24" :md="24" :lg="12">
                <a-form-item
                    :label="$t('basic_salary.divisor')"
                    name="divisorData"
                    :help="rules.divisorData ? rules.divisorData.message : null"
                    :validateStatus="rules.divisorData ? 'error' : null"
                    class="required"
                >
                    <a-input-number
                        v-model:value="formData.divisor"
                        :placeholder="
                            $t('common.placeholder_default_text', [
                                $t('basic_salary.divisor'),
                            ])
                        "
                        min="0"
                        style="width: 70%"
                        @change="calculateSalary"
                    >
                    </a-input-number>
                </a-form-item>
            </a-col>
        </a-row>
        <br>
        <a-row :gutter="16">
            <a-col :xs="24" :sm="12" :md="12" :lg="6" >
                <a-form-item
                    :label="$t('basic_salary.the_amount_hourly')"
                    name="hourlySalary"
                    :help="rules.hourlySalary ? rules.hourlySalary.message : null"
                    :validateStatus="rules.hourlySalary ? 'error' : null"
                    class="required"
                >

                <a-input-number
                    v-model:value="formData.hourly_rate"
                    :placeholder="
                        $t('common.placeholder_default_text', [$t('basic_salary.ctc')])
                    "
                    min="0"
                    :disabled="true"
                    style="width: 100%"
                >
                    <template #addonBefore>
                        {{ appSetting.currency.symbol }}
                    </template>
                </a-input-number>


                </a-form-item>
                
            </a-col>

            <a-col :xs="24" :sm="12" :md="4" :lg="6" class="column-text" style="flex: 1; text-align: left;">
            <a-form-item
                    :label="$t('basic_salary.the_amount_daily')"
                    name="dailySalary"
                    :help="rules.dailySalary ? rules.dailySalary.message : null"
                    :validateStatus="rules.dailySalary ? 'error' : null"
                    class="required"
                >


                <a-input-number
                    v-model:value="formData.daily_rate"
                    :placeholder="
                        $t('common.placeholder_default_text', [$t('basic_salary.ctc')])
                    "
                    min="0"
                    :disabled="true"
                    style="width: 100%"
                >
                    <template #addonBefore>
                        {{ appSetting.currency.symbol }}
                    </template>
                </a-input-number>
            
            </a-form-item>
                
            </a-col>

            <a-col :xs="24" :sm="12" :md="4" :lg="6" class="column-text" style="flex: 1; text-align: left;">
                <a-form-item
                        :label="$t('basic_salary.the_amount_semi_monthly')"
                        name="semi_monthlySalary"
                        :help="rules.semi_monthlySalary ? rules.semi_monthlySalary.message : null"
                        :validateStatus="rules.semi_monthlySalary ? 'error' : null"
                        class="required"
                    >


                    <a-input-number
                        v-model:value="formData.semi_monthly_rate"
                        :placeholder="
                            $t('common.placeholder_default_text', [$t('basic_salary.ctc')])
                        "
                        min="0"
                        :disabled="true"
                        style="width: 100%"
                    >
                        <template #addonBefore>
                            {{ appSetting.currency.symbol }}
                        </template>
                    </a-input-number>
                
                </a-form-item>
                
            </a-col>

            <a-col :xs="24" :sm="12" :md="6" :lg="6" class="column-text" style="flex: 1; text-align: left;">
                <a-form-item
                        :label="$t('basic_salary.annual_amount')"
                        name="annualSalary"
                        :help="rules.annualSalary ? rules.annualSalary.message : null"
                        :validateStatus="rules.annualSalary ? 'error' : null"
                        class="required"
                    >
                    <a-input-number
                        v-model:value="formData.annual_ctc"
                        :placeholder="
                            $t('common.placeholder_default_text', [$t('basic_salary.ctc')])
                        "
                        min="0"
                        :disabled="true"
                        style="width: 100%"
                    >
                        <template #addonBefore>
                            {{ appSetting.currency.symbol }}
                        </template>
                    </a-input-number>
                </a-form-item>
                
            </a-col>
        </a-row>

        <br>

        <a-row :gutter="16" class="new-class">
            <a-col :xs="24" :sm="24" :md="4" :lg="4">{{
                $t("basic_salary.other_info")
            }}</a-col>
        </a-row>
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24" class="column-text" style="flex: 1; text-align: left;">
                <a-form-item
                    :label="$t('basic_salary.sss_no')"
                    name="sss_no"
                    class="required"
                >
                    <a-input
                        v-model:value="formData.sss_no"
                        :placeholder="$t('common.placeholder_default_text', [$t('basic_salary.sss_no')])"
                        style="width: 100%"
                        @change="formatDataID('SSS')"
                    />
                </a-form-item>
            </a-col>

                <a-col :xs="24" :sm="24" :md="24" :lg="24" class="column-text" style="flex: 1; text-align: left;">
                    <a-form-item
                        :label="$t('basic_salary.philhealth_no')"
                        name="philhealth_no"
                        class="required"
                        
                    >
                        <a-input
                            v-model:value="formData.philhealth_no"
                            :placeholder="$t('common.placeholder_default_text', [$t('basic_salary.philhealth_no')])"
                            style="width: 100%"
                            @change="formatDataID('PhilHealth')"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="12" :lg="24" class="column-text" style="flex: 1; text-align: left;">
                    <a-form-item
                        :label="$t('basic_salary.pagibig_no')"
                        name="pagibig_no"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.pagibig_no"
                            :placeholder="$t('common.placeholder_default_text', [$t('basic_salary.pagibig_no')])"
                            style="width: 100%"
                            @change="formatDataID('pagibig')"
                        />
                    </a-form-item>
                </a-col>

                <a-col :xs="24" :sm="12" :md="12" :lg="24" class="column-text" style="flex: 1; text-align: left;">
                    <a-form-item
                        :label="$t('basic_salary.tin_no')"
                        name="tin_no"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.tin_no"
                            :placeholder="$t('common.placeholder_default_text', [$t('basic_salary.tin_no')])"
                            style="width: 100%"
                            @change="formatDataID('tin')"
                        />
                    </a-form-item>
                </a-col>
        </a-row>
        <br>

        <a-row :gutter="16" class="new-class">
            <a-col :xs="24" :sm="24" :md="4" :lg="4">{{
                $t("basic_salary.fixed_benefits")
            }}</a-col>
        </a-row>


        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24" class="column-text" style="flex: 1; text-align: left;">
                <a-form-item
                    :label="$t('basic_salary.sss_fixed')"
                    name="sss_fixed"
                    class="required"
                >
                    <a-input
                        v-model:value="formData.sss_fixed"
                        :placeholder="$t('common.placeholder_default_text', [$t('basic_salary.sss_fixed')])"
                        style="width: 100%"
                    />
                </a-form-item>
            </a-col>

                <a-col :xs="24" :sm="24" :md="24" :lg="24" class="column-text" style="flex: 1; text-align: left;">
                    <a-form-item
                        :label="$t('basic_salary.philhealth_fixed')"
                        name="philhealth_fixed"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.philhealth"
                            :placeholder="$t('common.placeholder_default_text', [$t('basic_salary.philhealth_fixed')])"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="12" :lg="24" class="column-text" style="flex: 1; text-align: left;">
                    <a-form-item
                        :label="$t('basic_salary.pagibig_fixed')"
                        name="pagibig_fixed"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.pagibig"
                            :placeholder="$t('common.placeholder_default_text', [$t('basic_salary.pagibig_fixed')])"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>

                <a-col :xs="24" :sm="12" :md="12" :lg="24" class="column-text" style="flex: 1; text-align: left;">
                    <a-form-item
                        :label="$t('basic_salary.tin_fixed')"
                        name="tin_fixed"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.tin"
                            :placeholder="$t('common.placeholder_default_text', [$t('basic_salary.tin_fixed')])"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>
        </a-row>

        

        
        
        
       
    </a-form>
</template>
<script>
import { SaveOutlined } from "@ant-design/icons-vue";
import { find, forEach } from "lodash-es";
import { computed, defineComponent, onMounted, ref, watch } from "vue";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";
import hrmManagement from "../../../../common/composable/hrmManagement";
import SalaryGroupAddButton from "../../settings/payroll-settings/salary-groups/AddButton.vue";


export default defineComponent({
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        user: {
            type: Object,
            default: {},
        },
        inputVisible: {
            type: Boolean,
            default: false,
        },
    },
    components: {
        SaveOutlined,
        UserInfo,
        SalaryGroupAddButton,
        hrmManagement
    },
    setup(props, { emit }) {
        const { loading, rules } = apiAdmin();
        const { appSetting } = common();
        // console.log('rules', rules)
        const selectUsers = ref("");
        const componentIds = ref([]);
        const formData = ref({
            basic_salary: 0,
            monthly_amount: 0,
            annual_amount: 0,
            hourly_amount: 0,
            daily_amount: 0,
            annual_ctc: 0,
            calculation_type: "monthly",
            daily_rate: 0,
            ctc_value: 0,
            divisor: 0,
            semi_monthlySalary: 0,
            sss_no: "",
            philhealth_no: "",
            pagibig_no: "",
            tin_no: "",
            sss_fixed: 0,
            philhealth_fixed: 0,
            pagibig_fixed: 0,
            tin_fixed: 0,


        });
        const { sssFormat, philhealthFormat, pagibigFormat,tinFormat } = hrmManagement();

        // console.log('formData', props.user.xid);
        const monthlySalary = ref(0);
        const semi_monthlySalary = ref(0);
        const divisorData = ref(0)
        const annualSalary = ref(0);
        const hourlySalary = ref(0);
        const dailySalary = ref(0);
        const earnings = ref(0);
        const deductions = ref(0);
        const monthlyCostToCompany = ref(0);
        const salaryComponents = ref([]);
        const salaryGroups = ref([]);
        const salaryGroupUrl = 
            "salary-groups?fields=id,xid,name,divisor,sss_no,salaryGroupComponents{id,xid,x_salary_group_id,x_salary_component_id},salaryGroupComponents:salaryComponent{id,xid,name,type,value_type,bi_weekly,weekly,monthly,semi_monthly},salaryGroupUsers{id,xid,x_user_id,x_salary_group_id},salaryGroupUsers:users{id,xid,name}";
        
        // console.log(props.user)
        const salaryGroupComponentProps = ref([]);
        onMounted(() => {
            fetchSalaryGroups();
            fetchDataSalary(props.user.xid);

        });
        

        const fetchDataSalary = async (id) => {
            if(id){
                // const userprofile_url = `employee-profile/${id}`;
                // const salaryProfile = await axiosAdmin.get(userprofile_url);
                // // const salaryProfileResponse = await salaryProfile.json();
                // const salaryProfileResponse = salaryProfile.data[0];
                //     formData.value = {
                //         ...formData.value, // keep existing values if needed
                //         calculation_type: salaryProfileResponse.calculation_type,
                //         ctc_value: salaryProfileResponse.monthly_amount|| 0, // if that's your main source
                //         monthly_amount: salaryProfileResponse.monthly_amount|| 0,
                //         hourly_amount: salaryProfileResponse.hourly_rate,
                //         daily_amount: salaryProfileResponse.daily_rate,
                //         annual_amount: salaryProfileResponse.annual_amount,
                //         divisor: salaryProfileResponse.divisor
                //     };
                calculateSalary()
                // console.log(salaryProfile)
                // Promise.all([salaryProfile]).then(([salaryProfileResponse]) => {
                //     // console.log(formData)
                //     let data_show = salaryProfileResponse.data[0];
                //     formData.value = {
                //         ...formData.value, // keep existing values if needed
                //         calculation_type: data_show.calculation_type,
                //         ctc_value: data_show.monthly_amount|| 0, // if that's your main source
                //         monthly_amount: data_show.monthly_amount|| 0,
                //         hourly_amount: data_show.hourly_rate,
                //         daily_amount: data_show.daily_rate,
                //         annual_amount: data_show.annual_amount,
                //         divisor: data_show.divisor
                //     };
                //     calculateSalary()
                // });
            }
            
        };

        const fetchSalaryGroups = () => {
            const salaryGroupPromise = axiosAdmin.get(salaryGroupUrl);

            Promise.all([salaryGroupPromise]).then(([salaryGroupResponse]) => {
                salaryGroups.value = salaryGroupResponse.data;
                
            });
        };

        const salaryGroupAdded = () => {
            axiosAdmin.get(salaryGroupUrl).then((response) => {
                salaryGroups.value = response.data;
            });
            
        };

        const fetchSalaryComponentsAndUsers = (salaryGroupId) => {
            if (!salaryGroupId) {
                salaryGroupComponentProps.value = [];
                calculateSalary();
                return;
            }

            axiosAdmin.get(salaryGroupUrl).then((response) => {
                const allSalaryGroups = response.data;
                // console.log('response', allSalaryGroups)
                const selectedGroup = allSalaryGroups.find(
                    (group) => group.xid === salaryGroupId
                );

                

                if (selectedGroup) {
                    salaryGroupComponentProps.value =
                        selectedGroup.salary_group_components;
                } else {
                    salaryGroupComponentProps.value = [];
                }

                calculateSalary();
            });
        };

        // Computed properties
        const specialAllowance = computed(() =>
            (
                
                Number(monthlySalary.value) -
                Number(earnings.value)
            ).toFixed(2)
        );

        const basicSalary = computed(() =>
            (
                Number(monthlySalary.value) +
                Number(specialAllowance.value) +
                Number(earnings.value) -
                Number(deductions.value)
            ).toFixed(2)
        );

        const netSalary = computed(() => {
            return (
                Number(formData.value.basic_salary) +
                Number(specialAllowance.value) +
                Number(earnings.value) -
                Number(deductions.value)
            ).toFixed(2);
        });
        
        const calculateSalary = () => {
                    const { calculation_type, ctc_value, annual_ctc, divisor, semi_monthly_rate, basic_salary } = formData.value;
                    console.log("data_fsadasdsadasdadaddsorm", formData.value)
                    switch (calculation_type) {
                        case "fixed":
                        case "s_monthly":
                        case "daily":
                        case "hourly":
                        case "monthly": 
                            // monthlySalary.value = ctc_value;
                            // semi_monthlySalary.value = (ctc_value / 2).toFixed(4);
                            formData.value.semi_monthly_rate = (basic_salary / 2).toFixed(4);
                            formData.value.annual_ctc = (basic_salary * 12).toFixed(4);
                            formData.value.divisor = divisor || 0;
                            formData.value.daily_rate = (basic_salary / divisor).toFixed(4);
                            formData.value.hourly_rate = (basic_salary / divisor / 8).toFixed(4);
                            formData.value.monthly_amount = basic_salary;
                            formData.value.ctc_value = basic_salary;
                            // formData.value.basic_salary = basic_salary;
                            // formData.value.annual_amount = (basic_salary * 12).toFixed(4);
                            // // semi_monthly = (ctc_value / 2).toFixed(4);
                            // semi_monthlySalary.value = (ctc_value / 2).toFixed(4);
                            // basicSalary.value = ctc_value;
                            // dailySalary.value = 0;
                            // hourlySalary.value = 0;
                            // annualSalary.value = (ctc_value * 12).toFixed(4);
                            // divisorData.value = divisor;
                            // annual_ctc = (ctc_value * 12).toFixed(4);
                            break;
                        default: {
                            monthlySalary.value = 0;
                            annualSalary.value = 0;
                            semi_monthlySalary.value = 0;
                        }
                    }
                    console.log("divisor", divisorData.value)
                    monthlyCostToCompany.value = (annual_ctc / 12).toFixed(2);

                    emit("updateSalaryData", {
                        ...formData.value,
                        xid: props.user.xid,
                        // basic_salary: monthlySalary.value,
                        // annual_amount: formData.value.annual_amount,
                        // divisor: divisorData.value,
                        // monthly_amount: monthlySalary.value,
                        // semi_monthly_rate: semi_monthlySalary.value,
                        // hourly_rate: hourlySalary.value,
                        // daily_rate: dailySalary.value,
                        salary_component_ids: componentIds.value,
                        special_allowances: specialAllowance.value,
                        salary_components: salaryComponents.value,
                        net_salary: 0,
                    });


                    
        };

        const formatDataID = (ident) => {
            console.log("formatDataID", ident);

            switch (ident) {
                case "SSS":
                    formData.value.sss_no = sssFormat(
                        formData.value.sss_no
                    );
                break;
                case "PhilHealth":
                    formData.value.philhealth_no = philhealthFormat(
                        formData.value.philhealth_no
                    );
                default:

                case "pagibig":
                    formData.value.pagibig_no = pagibigFormat(
                        formData.value.pagibig_no
                    );
                break;

                case "tin":
                    formData.value.tin_no = tinFormat(
                        formData.value.tin_no
                    );
                break;
            }


            emit("updateSalaryData", {
                        ...formData.value,
                xid: props.user.xid,
                
            });
        };
        




        const updateMonthlyValue = (value, component) => {
            if (!component) return;

            if (component.value_type === "variable") {
                component.monthly = parseFloat(value) || 0;

                const targetComponent = salaryComponents.value.find(
                    (item) => item.id === component.xid
                );

                if (targetComponent) {
                    targetComponent.monthly_value = component.monthly;
                } else {
                    salaryComponents.value.push({
                        id: component.xid,
                        type: component.type,
                        value_type: component.value_type,
                        monthly_value: component.monthly,
                    });
                }

                calculateSalary();
            }
        };


        watch(
            () => props.visible,
            (newVal, oldVal) => {
                fetchSalaryGroups();
                console.log("ID",props.user)
                fetchDataSalary(props.user.xid);
                if (newVal) {
                    formData.value = {
                        basic_salary: props.user.basic_salary || 0,
                        ctc_value: props.user.ctc_value || 0,
                        hourly_amount: props.user.hourly_amount || 0,
                        daily_amount: props.user.daily_amount || 0,
                        divisor: props.user.divisor,
                        calculation_type: props.user.calculation_type || "monthly",
                        annual_ctc: props.user.annual_ctc || 0,
                        monthly_amount: props.user.monthly_amount || 0,
                        annual_amount: props.user.annual_amount || 0,
                        salary_group_id: props.user.salary_group?.xid,
                        daily_rate: props.user.daily_rate || 0,
                        semi_monthly_rate: props.user.semi_monthly_rate || 0,
                        hourly_rate: props.user.hourly_rate || 0,
                        sss_no: props.user.sss_no || "",
                        philhealth_no: props.user.philhealth_no || "",
                        pagibig_no: props.user.pagibig_no || "",
                        tin_no: props.user.tin_no || "",
                        sss_fixed: props.user.sss_fixed || 0,
                        philhealth_fixed: props.user.philhealth_fixed || 0,
                        pagibig_fixed: props.user.pagibig_fixed || 0,
                        tin_fixed: props.user.tin_fixed || 0,
                    };

                    if (props.user.annual_ctc != 0 && props.user.annual_ctc != null) {
                        var allValues = [];

                        forEach(
                            props.user.salary_group?.salary_group_components,
                            (salComponent) => {
                                var findValueObject = find(
                                    props.user.basic_salary_details,
                                    {
                                        x_salary_component_id:
                                            salComponent.x_salary_component_id,
                                    }
                                );

                                if (findValueObject) {
                                    allValues.push({
                                        ...salComponent,
                                        salary_component: {
                                            ...salComponent.salary_component,
                                            monthly:
                                                findValueObject.value_type === "variable"
                                                    ? findValueObject.monthly
                                                    : salComponent.salary_component
                                                          .monthly,
                                        },
                                    });
                                } else {
                                    allValues.push(salComponent);
                                }
                            }
                        );

                        salaryGroupComponentProps.value = allValues;
                    } else {
                        salaryGroupComponentProps.value =
                            props.user?.salary_group?.salary_group_components || [];
                    }

                    // monthlySalary.value = props.user.monthly_amount || 0;
                    // annualSalary.value = props.user.annual_amount || 0;
                    formData.value.salary_group_id = props.user.salary_group?.xid;
                    if (props.user.salary_group) {
                        fetchSalaryComponentsAndUsers(props.user.salary_group.xid);
                    }
                    basicSalary.value = 0;
                    calculateSalary();
                }
            }
        );

        watch(
            [
                () => formData.value.annual_ctc,
                () => formData.value.ctc_value,
                () => earnings.value,
                () => deductions.value,
            ],
            () => {
                calculateSalary();
            }
        );

        return {
            loading,
            updateMonthlyValue,
            rules,
            formData,
            appSetting,
            selectUsers,
            monthlySalary,
            annualSalary,
            earnings,
            deductions,
            specialAllowance,
            basicSalary,
            hourlySalary,
            dailySalary,
            divisorData,
            semi_monthlySalary,
            calculateSalary,
            monthlyCostToCompany,
            salaryComponents,
            salaryGroupComponentProps,
            salaryGroups,
            fetchSalaryComponentsAndUsers,
            fetchDataSalary,
            salaryGroupAdded,
            sssFormat,
            philhealthFormat,
            formatDataID,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "60%",
        };
    },
});
</script>

<style scoped>
.column-text {
    text-align: left;
}
.styled-row {
    background-color: #f8f9fa;
    height: 55px;
    line-height: 55px;
    border-radius: 4px;
    margin-bottom: 10px;
    text-align: left;
}

.new-class {
    font-weight: bold;
}
</style>
