<template>
    <a-form>
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
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
        <!-- <a-row :gutter="16" :style="{ marginTop: inputVisible ? '0px' : '18px' }">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <a-form-item
                    :label="$t('basic_salary.annual_ctc')"
                    name="annual_ctc"
                    :help="rules.annual_ctc ? rules.annual_ctc.message : null"
                    :validateStatus="rules.annual_ctc ? 'error' : null"
                    :labelCol="{ span: 6 }"
                    :wrapperCol="{ span: 10 }"
                    labelAlign="left"
                >
                    <a-input-number
                        v-model:value="formData.annual_ctc"
                        :placeholder="
                            $t('common.placeholder_default_text', [
                                $t('basic_salary.annual_ctc'),
                            ])
                        "
                        style="width: 100%"
                        @change="calculateSalary"
                    >
                        <template #addonBefore>
                            {{ appSetting.currency.symbol }}
                        </template>
                    </a-input-number>
                </a-form-item>
            </a-col>
            <div :style="{ marginTop: inputVisible ? '0px' : '18px' }">
                {{ $t("basic_salary.cost_to_company_value_for_this_year") }}
            </div>
        </a-row> -->

        <!-- <a-row
            :gutter="16"
            style="
                margin-top: 20px;
                margin-bottom: 20px;
                border-bottom: 1px solid #d9d9d9;
                padding-bottom: 8px;
                display: flex;
                align-items: left;
                justify-content: space-between;
            "
        >
            <a-col
                :xs="24"
                :sm="12"
                :md="4"
                :lg="6"
                :class="['column-text', 'new-class']"
            >
                {{ $t("basic_salary.salary_component") }}
            </a-col>
            <a-col
                :xs="24"
                :sm="12"
                :md="4"
                :lg="6"
                :class="['column-text', 'new-class']"
            >
                {{ $t("Hourly Amount") }}
            </a-col>

            <a-col
                :xs="24"
                :sm="12"
                :md="4"
                :lg="6"
                :class="['column-text', 'new-class']"
            >
                {{ $t("Daily Amount") }}
            </a-col>

            <a-col
                :xs="24"
                :sm="12"
                :md="4"
                :lg="6"
                :class="['column-text', 'new-class']"
            >
                {{ $t("basic_salary.monthly_amount") }}
            </a-col>
            <a-col
                :xs="24"
                :sm="12"
                :md="4"
                :lg="6"
                :class="['column-text', 'new-class']"
            >
                {{ $t("basic_salary.annual_amount") }}
            </a-col>
        </a-row> -->

        <a-row
            style="
                margin-top: 20px;
                margin-bottom: 20px;
                border-bottom: 1px solid #d9d9d9;
                padding-bottom: 8px;
                display: flex;
                justify-content: space-between;
            "
            >
            <a-col :class="['column-text', 'new-class']" style="flex: 1; text-align: left;">
                {{ $t("basic_salary.salary_component") }}
            </a-col>

            <a-col :class="['column-text', 'new-class']" style="flex: 1; text-align: left;">
                {{ $t("basic_salary.the_amount") }}
            </a-col>

            <a-col :class="['column-text', 'new-class']" style="flex: 1; text-align: left;">
                {{ $t("basic_salary.the_amount_hourly") }}
            </a-col>
            <a-col :class="['column-text', 'new-class']" style="flex: 1; text-align: left;">
                {{ $t("basic_salary.the_amount_daily") }}
            </a-col>
            <a-col :class="['column-text', 'new-class']" style="flex: 1; text-align: left;">
                {{ $t("basic_salary.monthly_amount") }}
            </a-col>
            <a-col :class="['column-text', 'new-class']" style="flex: 1; text-align: left;">
                {{ $t("basic_salary.annual_amount") }}
            </a-col>
        </a-row>
 <!-- :gutter="16" -->
        <a-row
            
            style="
                margin-top: 20px;
                padding-bottom: 8px;
                display: flex;
                align-items: left;
            "
        >
            <!-- Salary Component -->
            <a-col  class="column-text" style="flex: 1; text-align: left;">
                {{ $t("basic_salary.basic_salary") }}
            </a-col>

            <!-- Calculation Type -->
            <a-col :xs="24" :sm="12" :md="4" :lg="6" class="column-text"  >
                <a-form-item
                    name="ctc_value"
                    :help="rules.ctc_value ? rules.ctc_value.message : null"
                    :validateStatus="rules.ctc_value ? 'error' : null"
                    class="required"
                >
                    <a-input-number
                        v-model:value="formData.ctc_value"
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
                                <!-- <a-select-option value="fixed">{{
                                    $t("basic_salary.fixed")
                                }}</a-select-option>
                                <a-select-option value="%_of_ctc">{{
                                    $t("basic_salary.%_of_ctc")
                                }}</a-select-option> -->
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

            <!-- Hourly Amount -->
            <a-col :xs="24" :sm="12" :md="4" :lg="6" class="column-text" style="flex: 1; text-align: left;" >
                <a-input-number
                    v-model:value="hourlySalary"
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
            </a-col>

            <!-- Daily Amount -->
            <a-col :xs="24" :sm="12" :md="4" :lg="6" class="column-text" style="flex: 1; text-align: left;">
                <a-input-number
                    v-model:value="dailySalary"
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
            </a-col>

            <!-- Monthly Amount -->
            <a-col :xs="24" :sm="12" :md="6" :lg="6" class="column-text" style="flex: 1; text-align: left;">
                <a-input-number
                    v-model:value="monthlySalary"
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
            </a-col>

            <!-- Annual Amount -->
            <a-col :xs="24" :sm="12" :md="6" :lg="6" class="column-text" style="flex: 1; text-align: left;">
                <a-input-number
                    v-model:value="annualSalary"
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
            </a-col>
        </a-row>
        <a-row :gutter="16" class="new-class">
            <a-col :xs="24" :sm="24" :md="4" :lg="4">{{
                $t("basic_salary.earnings")
            }}</a-col>
        </a-row>
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24" v-if="salaryGroupComponentProps">
                <div v-for="(component, idx) in salaryGroupComponentProps" :key="idx">
                    <!-- Check if the salary component is of type 'earnings' -->
                    <a-row
                        v-if="component.salary_component.type === 'earnings'"
                        :gutter="16"
                        style="margin-top: 14px"
                    >
                        <!-- Salary Component Name -->
                        <a-col :span="6" class="column-text">
                            <span>{{ component.salary_component.name }}</span>
                        </a-col>
                        


                        <!-- Monthly Input for Earnings -->
                        <a-col :span="6">
                            <span>
                                {{
                                    component.salary_component.value_type === "fixed"
                                        ? $t("salary_component.fixed")
                                        : component.salary_component.value_type ===
                                          "basic_percent"
                                        ? $t("salary_component.basic_percent")
                                        : component.salary_component.value_type ===
                                          "ctc_percent"
                                        ? $t("salary_component.ctc_percent")
                                        : $t("salary_component.variable")
                                }}
                            </span>
                        </a-col>
                        <a-col :span="6">
                            <a-input
                                :value="getMonthlyValue(component)"
                                @input="
                                    (event) =>
                                        updateMonthlyValue(
                                            event.target.value,
                                            component.salary_component
                                        )
                                "
                                :disabled="
                                    component.salary_component.value_type !== 'variable'
                                "
                                placeholder="Enter Monthly Value"
                                @change="calculateSalary"
                                style="width: 100%"
                            >
                                <template #addonBefore>
                                    {{ appSetting.currency.symbol }}
                                </template>
                            </a-input>
                        </a-col>

                        <!-- Annual Input (calculated) -->
                        <a-col :span="6">
                            <a-input
                                :value="calculateAnnualValue(component)"
                                :disabled="
                                    component.salary_component.value_type !== 'variable'
                                "
                                placeholder="Annual Value"
                                readonly
                                style="width: 100%"
                            >
                                <template #addonBefore>
                                    {{ appSetting.currency.symbol }}
                                </template>
                            </a-input>
                        </a-col>
                    </a-row>
                </div>
            </a-col>
        </a-row>

        <a-row :gutter="16" style="margin-top: 10px">
            <a-col :xs="24" :sm="24" :md="6" :lg="6" class="column-text">
                <div>{{ $t("basic_salary.special_allowances") }}</div>
            </a-col>

            <!-- Value Type or Description (Optional) -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6" class="column-text">
                <div>{{ $t("basic_salary.special_allowances") }}</div>
            </a-col>

            <!-- Special Allowance Monthly Input -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6" class="column-text">
                <span style="display: inline-block; width: 100%">
                    {{ appSetting.currency.symbol }}
                    {{ specialAllowance }}</span
                >
            </a-col>

            <!-- Special Allowance Annual Input -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6" class="column-text">
                <span style="display: inline-block; width: 100%">
                    {{ appSetting.currency.symbol }}
                    {{ (specialAllowance * 12).toFixed(2) }}</span
                >
            </a-col>
        </a-row>

        <a-row :gutter="[16, 24]" style="margin-top: 10px" class="styled-row">
            <!-- Cost to Company Label -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6" class="column-text">
                <div>{{ $t("basic_salary.cost_to_company") }}</div>
            </a-col>
            <a-col :xs="24" :sm="24" :md="6" :lg="6"> </a-col>

            <!-- Monthly Cost to Company Value -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6">
                <b style="display: inline-block; width: 100%">
                    {{ appSetting.currency.symbol }}{{ monthlyCostToCompany }}
                </b>
            </a-col>

            <!-- Annual Cost to Company Value -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6">
                <b style="display: inline-block; width: 100%">
                    {{ appSetting.currency.symbol }}
                    {{ formData.annual_ctc.toFixed(2) }}
                </b>
            </a-col>
        </a-row>
        <a-row :gutter="16" style="margin-top: 20px" class="new-class">
            <a-col :xs="24" :sm="24" :md="4" :lg="4">{{
                $t("basic_salary.deductions")
            }}</a-col>
        </a-row>
        <a-row :gutter="[16, 24]">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <div v-for="(component, idx) in salaryGroupComponentProps" :key="idx">
                    <a-row
                        v-if="component.salary_component.type === 'deductions'"
                        :gutter="16"
                        style="margin-top: 10px"
                    >
                        <a-col :span="6" class="column-text">
                            <span>{{ component.salary_component.name }}</span>
                        </a-col>


                        <a-col :span="6">
                            <span>
                                {{
                                    component.salary_component.value_type === "fixed"
                                        ? $t("salary_component.fixed")
                                        : component.salary_component.value_type ===
                                          "basic_percent"
                                        ? $t("salary_component.basic_percent")
                                        : component.salary_component.value_type ===
                                          "ctc_percent"
                                        ? $t("salary_component.ctc_percent")
                                        : $t("salary_component.variable")
                                }}
                            </span>
                        </a-col>

                        <a-col :span="6">
                            <a-input
                                :value="getMonthlyValue(component)"
                                @input="
                                    (event) =>
                                        updateMonthlyValue(
                                            event.target.value,
                                            component.salary_component
                                        )
                                "
                                :disabled="
                                    component.salary_component.value_type !== 'variable'
                                "
                                placeholder="Enter Monthly Value"
                                @change="calculateSalary"
                                style="width: 100%"
                            >
                                <template #addonBefore>
                                    {{ appSetting.currency.symbol }}
                                </template>
                            </a-input>
                        </a-col>

                        <a-col :span="6">
                            <a-input
                                :value="calculateAnnualValue(component)"
                                :disabled="
                                    component.salary_component.value_type !== 'variable'
                                "
                                placeholder="Annual Value"
                                readonly
                                style="
                                    width: 100%;
                                    border: none;
                                    background: transparent;
                                    color: inherit;
                                    padding: 0;
                                "
                            >
                                <template #addonBefore>
                                    {{ appSetting.currency.symbol }}
                                </template>
                            </a-input>
                        </a-col>
                    </a-row>
                </div>
            </a-col>
        </a-row>
        <a-row :gutter="[16, 24]" style="margin-top: 10px" class="styled-row">
            <!-- Cost to Company Label -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6" class="column-text">
                <div>{{ $t("basic_salary.total_deductions") }}</div>
            </a-col>
            <a-col :xs="24" :sm="24" :md="6" :lg="6"> </a-col>

            <!-- Monthly Cost to Company Value -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6">
                <div>{{ appSetting.currency.symbol }}{{ deductions.toFixed(2) }}</div>
            </a-col>

            <!-- Annual Cost to Company Value -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6">
                <div>
                    {{ appSetting.currency.symbol }}
                    {{ (deductions * 12).toFixed(2) }}
                </div>
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
            ctc_value: 0,
        });
        // console.log('formData', props.user.xid);
        const monthlySalary = ref(0);
        const annualSalary = ref(0);
        const hourlySalary = ref(0);
        const dailySalary = ref(0);
        const earnings = ref(0);
        const deductions = ref(0);
        const monthlyCostToCompany = ref(0);
        const salaryComponents = ref([]);
        const salaryGroups = ref([]);
        let semi_monthly = 0;
        const salaryGroupUrl = 
            "salary-groups?fields=id,xid,name,salaryGroupComponents{id,xid,x_salary_group_id,x_salary_component_id},salaryGroupComponents:salaryComponent{id,xid,name,type,value_type,bi_weekly,weekly,monthly,semi_monthly},salaryGroupUsers{id,xid,x_user_id,x_salary_group_id},salaryGroupUsers:users{id,xid,name}";
        
        // console.log(props.user)
        const salaryGroupComponentProps = ref([]);
        onMounted(() => {
            fetchSalaryGroups();
            fetchDataSalary(props.user.xid);

        });

        const fetchDataSalary = (id) => {
            if(id){
                const userprofile_url = `employee-profile/${id}`;
                const salaryProfile = axiosAdmin.get(userprofile_url);
                Promise.all([salaryProfile]).then(([salaryProfileResponse]) => {
                    console.log(formData)
                    let data_show = salaryProfileResponse.data[0];
                    formData.value = {
                        ...formData.value, // keep existing values if needed
                        calculation_type: data_show.calculation_type,
                        ctc_value: data_show.monthly_amount|| 0, // if that's your main source
                        monthly_amount: data_show.monthly_amount|| 0,
                        hourly_amount: data_show.hourly_rate,
                        daily_amount: data_show.daily_rate,
                        annual_amount: data_show.annual_amount
                    };
                    // monthlySalary.value = salaryProfileResponse.data[0].monthly_amount;
                    // dailySalary.value = salaryProfileResponse.data[0].daily_rate;
                    // hourlySalary.value = salaryProfileResponse.data[0].hourly_rate
                    // annualSalary.value = salaryProfileResponse.data[0].annual_amount
                    calculateSalary()
                });
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
                // Number(monthlyCostToCompany.value) -
                // Number(monthlySalary.value) -
                // Number(earnings.value)
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
        
//DEDUCTION AND EARNINGS COMPUTATION

        const calculateEarningsAndDeductions = () => {
            earnings.value = 0;
            deductions.value = 0;
            componentIds.value = [];
            salaryComponents.value = [];

            salaryGroupComponentProps.value.forEach(({ salary_component, xid }) => {
                let amount = 0;

                switch (salary_component.value_type) {
                    case "fixed": {
                                  const name = salary_component.name;
                                
                                  if (name === "Pag-IBIG >5000") {
                                    amount = Number(salary_component.monthly) || 0;  // This will be 200
                                  } else {
                                    amount = Number(salary_component.monthly) || 0;
                                  }
                                
                                  break;
                                }

                        const salary = monthlySalary.value;
                        const name = salary_component.name;
                        if (name === "SSS") {
                            const sssTable = [
                                { min: 0, max: 2249.99, contribution: 80 },
                                { min: 2250, max: 2749.99, contribution: 100 },
                                { min: 2750, max: 3249.99, contribution: 120 },
                                { min: 3250, max: 3749.99, contribution: 140 },
                                { min: 3750, max: 4249.99, contribution: 160 },
                                { min: 4250, max: 4749.99, contribution: 180 },
                                { min: 4750, max: 5249.99, contribution: 200 },
                                { min: 5250, max: 5749.99, contribution: 220 },
                                { min: 5750, max: 6249.99, contribution: 240 },
                                { min: 6250, max: 6749.99, contribution: 260 },
                                { min: 6750, max: 7249.99, contribution: 280 },
                                { min: 7250, max: 7749.99, contribution: 300 },
                                { min: 7750, max: 8249.99, contribution: 320 },
                                { min: 8250, max: 8749.99, contribution: 340 },
                                { min: 8750, max: 9249.99, contribution: 360 },
                                { min: 9250, max: 9749.99, contribution: 380 },
                                { min: 9750, max: 10249.99, contribution: 400 },
                                { min: 10250, max: 10749.99, contribution: 420 },
                                { min: 10750, max: 11249.99, contribution: 440 },
                                { min: 11250, max: 11749.99, contribution: 460 },
                                { min: 11750, max: 12249.99, contribution: 480 },
                                { min: 12250, max: 12749.99, contribution: 500 },
                                { min: 12750, max: 13249.99, contribution: 520 },
                                { min: 13250, max: 13749.99, contribution: 540 },
                                { min: 13750, max: 14249.99, contribution: 560 },
                                { min: 14250, max: 14749.99, contribution: 580 },
                                { min: 14750, max: 15249.99, contribution: 600 },
                                { min: 15250, max: 15749.99, contribution: 620 },
                                { min: 15750, max: 16249.99, contribution: 640 },
                                { min: 16250, max: 16749.99, contribution: 660 },
                                { min: 16750, max: 17249.99, contribution: 680 },
                                { min: 17250, max: 17749.99, contribution: 700 },
                                { min: 17750, max: 18249.99, contribution: 720 },
                                { min: 18250, max: 18749.99, contribution: 740 },
                                { min: 18750, max: 19249.99, contribution: 760 },
                                { min: 19250, max: 19749.99, contribution: 780 },
                                { min: 19750, max: 20249.99, contribution: 800 },
                                { min: 20250, max: 20749.99, contribution: 820 },
                                { min: 20750, max: 21249.99, contribution: 840 },
                                { min: 21250, max: 21749.99, contribution: 860 },
                                { min: 21750, max: 22249.99, contribution: 880 },
                                { min: 22250, max: 22749.99, contribution: 900 },
                                { min: 22750, max: 23249.99, contribution: 920 },
                                { min: 23250, max: 23749.99, contribution: 940 },
                                { min: 23750, max: 24249.99, contribution: 960 },
                                { min: 24250, max: 24749.99, contribution: 980 },
                                { min: 24750, max: 25249.99, contribution: 1000 },
                                { min: 25250, max: 25749.99, contribution: 1020 },
                                { min: 25750, max: 26249.99, contribution: 1040 },
                                { min: 26250, max: 26749.99, contribution: 1060 },
                                { min: 26750, max: 27249.99, contribution: 1080 },
                                { min: 27250, max: 27749.99, contribution: 1100 },
                                { min: 27750, max: 28249.99, contribution: 1120 },
                                { min: 28250, max: 28749.99, contribution: 1140 },
                                { min: 28750, max: 29249.99, contribution: 1160 },
                                { min: 29250, max: 29749.99, contribution: 1180 },
                                { min: 29750, max: 30249.99, contribution: 1200 },
                                { min: 30250, max: 30749.99, contribution: 1220 },
                                { min: 30750, max: 31249.99, contribution: 1240 },
                                { min: 31250, max: 31749.99, contribution: 1260 },
                                { min: 31750, max: 32249.99, contribution: 1280 },
                                { min: 32250, max: 32749.99, contribution: 1300 },
                                { min: 32750, max: 33249.99, contribution: 1320 },
                                { min: 33250, max: 33749.99, contribution: 1340 },
                                { min: 33750, max: 34249.99, contribution: 1360 },
                                { min: 34250, max: 34749.99, contribution: 1380 },
                                { min: 34750, max: 35249.99, contribution: 1400 },
                                { min: 35250, max: 35749.99, contribution: 1420 },
                                { min: 35750, max: 36249.99, contribution: 1440 },
                                { min: 36250, max: 36749.99, contribution: 1460 },
                                { min: 36750, max: 37249.99, contribution: 1480 },
                                { min: 37250, max: 37749.99, contribution: 1500 },
                                { min: 37750, max: 38249.99, contribution: 1520 },
                                { min: 38250, max: 38749.99, contribution: 1540 },
                                { min: 38750, max: 39249.99, contribution: 1560 },
                                { min: 39250, max: 39749.99, contribution: 1580 },
                                { min: 39750, max: 40249.99, contribution: 1600 },
                                { min: 40250, max: 40749.99, contribution: 1620 },
                                { min: 40750, max: Infinity, contribution: 1640 } // Max cap
                            ];
                            const sssContribution = sssTable.find(range => salary >= range.min && salary <= range.max)?.contribution || 1150;
                            amount = sssContribution;
                        }    case "variable":
                   
                      
                        break;

                    case "basic_percent": {
                      const salary = monthlySalary.value;
                      const name = salary_component.name;
                      const rate = Number(salary_component.monthly); // Assuming this is the percentage
                    
                      if (name === "Withholding Tax Sup") {
                        const lowerLimit = 20834;
                        const upperLimit = 33333;
                        if (salary >= lowerLimit && salary <= upperLimit) {
                          const cappedSalary = Math.min(Math.max(salary, lowerLimit), upperLimit);
                          const taxableAmount = cappedSalary - lowerLimit;
                          amount = (taxableAmount * rate) / 100 || 0;
                        } else {
                          amount = 0;
                        }
                    
                      } else if (name === "Withholding Tax Admin") {
                        const lowerLimit = 33334;
                        const upperLimit = 66666;
                        const baseTax = 2500;
                        if (salary >= lowerLimit && salary <= upperLimit) {
                          const cappedSalary = Math.min(Math.max(salary, lowerLimit), upperLimit);
                          const taxableAmount = cappedSalary - lowerLimit;
                          amount = baseTax + (taxableAmount * rate) / 100 || 0;
                        } else {
                          amount = 0;
                        }
                    
                      } else if (name === "Withholding Tax Guard") {
                        const lowerLimit = 0;
                        const upperLimit = 20833;
                        if (salary >= lowerLimit && salary <= upperLimit) {
                          amount = 0; // not taxable
                        } else {
                          amount = 0;
                        }
                    
                      } else if (name === "Withholding Tax ViP") {
                        const lowerLimit = 66668;
                        const upperLimit = 166667;
                        const baseTax = 10833;
                        if (salary >= lowerLimit && salary <= upperLimit) {
                          const cappedSalary = Math.min(Math.max(salary, lowerLimit), upperLimit);
                          const taxableAmount = cappedSalary - lowerLimit;
                          amount = baseTax + (taxableAmount * rate) / 100 || 0;
                        } else {
                          amount = 0;
                        }
                    
                      } else if (name === "Pag-IBIG <5000") {
                      const pagibigCap = 5000;
                    
                      if (salary < pagibigCap) {
                        // If salary is below ₱5,000, apply 2% of actual salary
                        amount = (salary * rate) / 100 || 0;
                      } else {
                        // If salary is ₱5,000 or more, cap the employee contribution at ₱100
                        amount = 100;
                      }
                    }
                    
                    else if (name === "PhilHealth") {
                      const rate = Number(salary_component.monthly) || 5; // Usually 5%
                      const min = 10000;
                      const max = 100000;
                      const cappedSalary = Math.min(Math.max(salary, min), max);
                      amount = (cappedSalary * rate / 100) / 2 || 0;
                    }
                      
                      
                      else {
                        amount = 0;
                      }
                    
                      break;
                    }


                    case "ctc_percent":
                        amount =
                            (monthlySalary.value * Number(salary_component.monthly)) /
                                formData.value.ctc_value || 0;
                        break;

                    default:
                        amount = 0;
                        break;
                }

                if (salary_component.type === "earnings") {
                    earnings.value += amount;
                } else if (salary_component.type === "deductions") {
                    deductions.value += amount;
                }

                salaryComponents.value.push({
                    id: salary_component.xid,
                    type: salary_component.type,
                    value_type: salary_component.value_type,
                    monthly_value: amount,
                });

                componentIds.value.push(xid);
            });
        };
        
        
//NOT MUCH CHANGES
        // const calculateSalary = () => {
        //     calculateEarningsAndDeductions();

        //     const { calculation_type, ctc_value, annual_ctc } = formData.value;
        //     console.log(formData.value)

        //     if (calculation_type === "fixed") {
        //         monthlySalary.value = ctc_value;
        //         annualSalary.value = ctc_value * 12;
        //     } else if (calculation_type === "%_of_ctc") {
        //         const percentage = Number(ctc_value);
        //         monthlySalary.value = ((annual_ctc * percentage) / 100 / 12).toFixed(2);
        //         annualSalary.value = ((annual_ctc * percentage) / 100).toFixed(2);
        //     }

        //     monthlyCostToCompany.value = (annual_ctc / 12).toFixed(2);

        //     emit("updateSalaryData", {
        //         ...formData.value,
        //         xid: props.user.xid,
        //         basic_salary: monthlySalary.value,
        //         annual_amount: annualSalary.value,
        //         monthly_amount: basicSalary.value,
        //         salary_component_ids: componentIds.value,
        //         special_allowances: specialAllowance.value,
        //         salary_components: salaryComponents.value,
        //         net_salary: netSalary.value,
        //     });
        // };


const calculateSalary = () => {
            calculateEarningsAndDeductions();

            const { calculation_type, ctc_value, annual_ctc } = formData.value;
            console.log(annual_ctc)

            // if (calculation_type === "fixed") {
            //     monthlySalary.value = ctc_value;
            //     annualSalary.value = ctc_value * 12;
            // } else if (calculation_type === "%_of_ctc") {
            //     const percentage = Number(ctc_value);
            //     monthlySalary.value = ((annual_ctc * percentage) / 100 / 12).toFixed(2);
            //     annualSalary.value = ((annual_ctc * percentage) / 100).toFixed(2);
            // }
            console.log(calculation_type)
            switch (calculation_type) {
                // case "fixed": {
                //     monthlySalary.value = ctc_value;
                //     annualSalary.value = ctc_value * 12;
                //     break;
                // }
                // case "%_of_ctc": {
                //     const percentage = Number(ctc_value);
                //     monthlySalary.value = ((annual_ctc * percentage) / 100 / 12).toFixed(2);
                //     annualSalary.value = ((annual_ctc * percentage) / 100).toFixed(2);
                //     break;
                // }
                case "fixed":
                case "s_monthly":
                case "daily":
                case "hourly":
                case "monthly": 
                    monthlySalary.value = ctc_value;
                    semi_monthly = (ctc_value / 2).toFixed(4);
                    basicSalary.value = ctc_value;
                    dailySalary.value = (ctc_value / 24).toFixed(4);
                    hourlySalary.value = ((ctc_value / 24) / 8).toFixed(4);
                    annualSalary.value = (ctc_value * 12).toFixed(4);
                    // annual_ctc = (ctc_value * 12).toFixed(4);
                    break;
                default: {
                    monthlySalary.value = 0;
                    annualSalary.value = 0;
                }
            }

            monthlyCostToCompany.value = (annual_ctc / 12).toFixed(2);

            emit("updateSalaryData", {
                ...formData.value,
                xid: props.user.xid,
                basic_salary: monthlySalary.value,
                annual_amount: annualSalary.value,
                // monthly_amount: basicSalary.value,
                monthly_amount: monthlySalary.value,
                semi_monthly_rate: semi_monthly,
                hourly_rate: hourlySalary.value,
                daily_rate: dailySalary.value,
                salary_component_ids: componentIds.value,
                special_allowances: specialAllowance.value,
                // annual_ctc: annual_ctc,
                // special_allowances: 0,
                salary_components: salaryComponents.value,
                net_salary: netSalary.value,
            });
        };
//SALARY COMPUTATION 
    const getMonthlyValue = (component) => {
        if (!component) return;

        const { value_type, monthly, name } = component.salary_component;
        const { ctc_value } = formData.value;

        switch (value_type) {
            case "fixed":{
            }
            case "variable": {
                const salary = monthlySalary.value;

            if (name === "SSS") {
                const sssTable = [
                { min: 0, max: 2249.99, contribution: 80 },
                { min: 2250, max: 2749.99, contribution: 100 },
                { min: 2750, max: 3249.99, contribution: 120 },
                { min: 3250, max: 3749.99, contribution: 140 },
                { min: 3750, max: 4249.99, contribution: 160 },
                { min: 4250, max: 4749.99, contribution: 180 },
                { min: 4750, max: 5249.99, contribution: 200 },
                { min: 5250, max: 5749.99, contribution: 220 },
                { min: 5750, max: 6249.99, contribution: 240 },
                { min: 6250, max: 6749.99, contribution: 260 },
                { min: 6750, max: 7249.99, contribution: 280 },
                { min: 7250, max: 7749.99, contribution: 300 },
                { min: 7750, max: 8249.99, contribution: 320 },
                { min: 8250, max: 8749.99, contribution: 340 },
                { min: 8750, max: 9249.99, contribution: 360 },
                { min: 9250, max: 9749.99, contribution: 380 },
                { min: 9750, max: 10249.99, contribution: 400 },
                { min: 10250, max: 10749.99, contribution: 420 },
                { min: 10750, max: 11249.99, contribution: 440 },
                { min: 11250, max: 11749.99, contribution: 460 },
                { min: 11750, max: 12249.99, contribution: 480 },
                { min: 12250, max: 12749.99, contribution: 500 },
                { min: 12750, max: 13249.99, contribution: 520 },
                { min: 13250, max: 13749.99, contribution: 540 },
                { min: 13750, max: 14249.99, contribution: 560 },
                { min: 14250, max: 14749.99, contribution: 580 },
                { min: 14750, max: 15249.99, contribution: 600 },
                { min: 15250, max: 15749.99, contribution: 620 },
                { min: 15750, max: 16249.99, contribution: 640 },
                { min: 16250, max: 16749.99, contribution: 660 },
                { min: 16750, max: 17249.99, contribution: 680 },
                { min: 17250, max: 17749.99, contribution: 700 },
                { min: 17750, max: 18249.99, contribution: 720 },
                { min: 18250, max: 18749.99, contribution: 740 },
                { min: 18750, max: 19249.99, contribution: 760 },
                { min: 19250, max: 19749.99, contribution: 780 },
                { min: 19750, max: 20249.99, contribution: 800 },
                { min: 20250, max: 20749.99, contribution: 820 },
                { min: 20750, max: 21249.99, contribution: 840 },
                { min: 21250, max: 21749.99, contribution: 860 },
                { min: 21750, max: 22249.99, contribution: 880 },
                { min: 22250, max: 22749.99, contribution: 900 },
                { min: 22750, max: 23249.99, contribution: 920 },
                { min: 23250, max: 23749.99, contribution: 940 },
                { min: 23750, max: 24249.99, contribution: 960 },
                { min: 24250, max: 24749.99, contribution: 980 },
                { min: 24750, max: 25249.99, contribution: 1000 },
                { min: 25250, max: 25749.99, contribution: 1020 },
                { min: 25750, max: 26249.99, contribution: 1040 },
                { min: 26250, max: 26749.99, contribution: 1060 },
                { min: 26750, max: 27249.99, contribution: 1080 },
                { min: 27250, max: 27749.99, contribution: 1100 },
                { min: 27750, max: 28249.99, contribution: 1120 },
                { min: 28250, max: 28749.99, contribution: 1140 },
                { min: 28750, max: 29249.99, contribution: 1160 },
                { min: 29250, max: 29749.99, contribution: 1180 },
                { min: 29750, max: 30249.99, contribution: 1200 },
                { min: 30250, max: 30749.99, contribution: 1220 },
                { min: 30750, max: 31249.99, contribution: 1240 },
                { min: 31250, max: 31749.99, contribution: 1260 },
                { min: 31750, max: 32249.99, contribution: 1280 },
                { min: 32250, max: 32749.99, contribution: 1300 },
                { min: 32750, max: 33249.99, contribution: 1320 },
                { min: 33250, max: 33749.99, contribution: 1340 },
                { min: 33750, max: 34249.99, contribution: 1360 },
                { min: 34250, max: 34749.99, contribution: 1380 },
                { min: 34750, max: 35249.99, contribution: 1400 },
                { min: 35250, max: 35749.99, contribution: 1420 },
                { min: 35750, max: 36249.99, contribution: 1440 },
                { min: 36250, max: 36749.99, contribution: 1460 },
                { min: 36750, max: 37249.99, contribution: 1480 },
                { min: 37250, max: 37749.99, contribution: 1500 },
                { min: 37750, max: 38249.99, contribution: 1520 },
                { min: 38250, max: 38749.99, contribution: 1540 },
                { min: 38750, max: 39249.99, contribution: 1560 },
                { min: 39250, max: 39749.99, contribution: 1580 },
                { min: 39750, max: 40249.99, contribution: 1600 },
                { min: 40250, max: 40749.99, contribution: 1620 },
                { min: 40750, max: Infinity, contribution: 1640 } // Max cap
                ];

                const bracket = sssTable.find(b => salary >= b.min && salary <= b.max);
                return bracket ? bracket.contribution : 0;
            }

        // Default return for other variable-based deductions (like % or fixed)
        return Number(monthly) || 0;
    }


      

    case "basic_percent": {
    const salary = monthlySalary.value;

        if (name === "Withholding Tax Sup") {
            const lowerLimit = 20834;
            const upperLimit = 33333;
            if (salary >= lowerLimit && salary <= upperLimit) {
            const cappedSalary = Math.min(Math.max(salary, lowerLimit), upperLimit);
            const taxablePortion = cappedSalary - lowerLimit;
            return (taxablePortion * Number(monthly)) / 100 || 0;
            }
            return 0;
        } else if (name === "Withholding Tax Admin") {
            const lowerLimit = 33334;
            const upperLimit = 66666;
            const baseTax = 2500;
            
            if (salary >= lowerLimit && salary <= upperLimit) {
            const cappedSalary = Math.min(Math.max(salary, lowerLimit), upperLimit);
            const taxablePortion = cappedSalary - lowerLimit;
            return baseTax + (taxablePortion * Number(monthly)) / 100 || 0;
            }
            return 0;
        } else if (name === "Withholding Tax Guard") {
            const lowerLimit = 0;
            const upperLimit = 20833;
            if (salary >= lowerLimit && salary <= upperLimit) {
            return 0; // this range is not taxable
            }
            return 0;

        } 
        
        else if (name === "Withholding Tax ViP") {
            const lowerLimit = 66668 ;
            const upperLimit = 166667;
            const baseTax = 10833;
            
            if (salary >= lowerLimit && salary <= upperLimit) {
            const cappedSalary = Math.min(Math.max(salary, lowerLimit), upperLimit);
            const taxablePortion = cappedSalary - lowerLimit;
            return baseTax + (taxablePortion * Number(monthly)) / 100 || 0;
            }
            return 0;
        }

        else if (name === "Pag-IBIG <5000") {
        const pagibigCap = 5000;
        const rate = Number(monthly);

        if (salary < pagibigCap) {
            return (salary * rate) / 100 || 0;
        } else {
            return 100;
        }
        }

        else if (name === "PhilHealth") {
            const rate = Number(monthly) || 5; // Usually 5%
            const min = 10000;
            const max = 100000;
            const cappedSalary = Math.min(Math.max(salary, min), max);
            return (cappedSalary * rate / 100) / 2 || 0;
        }
        
        return 0;
    }


    case "ctc_percent":
      return (monthlySalary.value * Number(monthly)) / ctc_value || 0;

    default:
    return 0;
  }
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

        const calculateAnnualValue = (component) => {
            return (getMonthlyValue(component) * 12).toFixed(2);
        };

        watch(
            () => props.visible,
            (newVal, oldVal) => {
                fetchSalaryGroups();
                // console.log("ID",props.user.xid)
                fetchDataSalary(props.user.xid);
                if (newVal) {
                    formData.value = {
                        basic_salary: props.user.basic_salary || 0,
                        ctc_value: props.user.ctc_value || 0,
                        hourly_amount: props.user.hourly_amount || 0,
                        daily_amount: props.user.daily_amount || 0,
                        calculation_type: props.user.calculation_type || "monthly",
                        annual_ctc: props.user.annual_ctc || 0,
                        monthly_amount: props.user.monthly_amount || 0,
                        annual_amount: props.user.annual_amount || 0,
                        salary_group_id: props.user.salary_group?.xid,
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

                    monthlySalary.value = props.user.monthly_amount || 0;
                    annualSalary.value = props.user.annual_amount || 0;
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
            getMonthlyValue,
            updateMonthlyValue,
            calculateAnnualValue,
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
            calculateSalary,
            monthlyCostToCompany,
            salaryComponents,
            salaryGroupComponentProps,
            salaryGroups,
            fetchSalaryComponentsAndUsers,
            fetchDataSalary,
            salaryGroupAdded,

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
