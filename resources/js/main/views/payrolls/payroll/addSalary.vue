<template>
    
    <!-- <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
    
        <a-form layout="vertical">
            <div style="background: #ececec; padding: 20px">
                <a-row :gutter="16">
                    
                    <a-col :span="12">
                        <a-card>
                            <a-statistic
                                :title="$t('payroll.month')"
                                :value="`${getMonthNameByNumber(data.month)} ${
                                    data.cut_off + ` ` + data.year
                                }`"
                                :value-style="{ color: '#3f8600' }"
                            />
                        </a-card>
                    </a-col>
                    <a-col :span="12">
                        <a-card>
                            <a-statistic
                                :title="$t('payroll.net_salary')"
                                :value="formatAmountCurrency(data.net_salary)"
                                :value-style="{ color: '#3f8600' }"
                            />
                        </a-card>
                    </a-col>
                </a-row>
            </div>
            <a-row class="mt-30">
                <a-col :span="12" :offset="6">
                    <a-tabs v-model:activeKey="activeKey">
                        <a-tab-pane key="summary" :tab="`${$t('payroll.summary')}`">
                            <a-table
                                :dataSource="summaryData"
                                :columns="summaryColumns"
                                :pagination="false"
                                size="middle"
                                :showHeader="false"
                            />
                        </a-tab-pane>
                        <a-tab-pane
                            key="leave"
                            :tab="`${$t('payroll.leaves')} / ${$t('payroll.holiday')}`"
                        >
                            <a-table
                                :dataSource="leaveHoliday"
                                :columns="summaryColumns"
                                :pagination="false"
                                size="middle"
                                :showHeader="false"
                            />
                        </a-tab-pane>
                    </a-tabs>
                </a-col>
            </a-row>

            <a-row class="mt-30" :gutter="16">
                <a-col :xs="24" :sm="24" :md="10" :lg="10">
                    <a-table
                        :dataSource="salaryComponentsData"
                        :columns="salaryComponentsColumns"
                        :pagination="false"
                        size="middle"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex == 'value'">
                                <a-typography-text
                                    v-if="record.key == 'pre_payment_amount'"
                                    type="danger"
                                >
                                    - {{ formatAmountCurrency(record.value) }}
                                </a-typography-text>
                                <a-typography-text
                                    v-else-if="
                                        record.key == 'expense_amount' ||
                                        record.key == 'salary_amount'
                                    "
                                    type="success"
                                >
                                    + {{ formatAmountCurrency(record.value) }}
                                </a-typography-text>
                            </template>
                        </template>
                    </a-table>
                </a-col>

                <a-col :xs="24" :sm="24" :md="14" :lg="14"> </a-col>
            </a-row>
        </a-form>
    </a-drawer> -->
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
    <!-- <pre>{{ data.payroll_detl }}</pre> -->
        <div class="payslip">
            <div class="section-title">Earnings</div>
            <table>
            <tr class="total">
                <td>Earnings</td>
                <td class="right">Amount</td>
            </tr>
            <tr>
                <td>Basic Salary</td>
                <td class="right">{{ formatCurrency(earnings.basic) }}</td>
            </tr>
            <tr class="total">
                <td>Overtime</td>
                <td class="right"></td>
            </tr>
            <tr v-for="(amount, label) in otherearnings" :key="label">
                <td>{{ label }}</td>
                <td class="right">{{ formatCurrency(amount) }}</td>
            </tr>
            <tr class="section-title">
                <td>Total Overtime Pay</td>
                <td class="right">{{ formatCurrency(totalOT) }}</td>
            </tr>

            <tr class="total" v-if="Object.keys(OtherearningsTax).length > 0">
                <td>Earnings Tax</td>
                <td class="right"></td>
            </tr>
            

            <tr v-for="(amount, label) in OtherearningsTax" :key="label">
                <td>{{ label }}</td>
                <td class="right">{{ formatCurrency(amount) }}</td>
            </tr>


            <tr class="total" v-if="Object.keys(OtherrearningsNonTax).length > 0">
                <td>Earnings Non Tax</td>
                <td class="right"></td>
            </tr>
            

            <tr v-for="(amount, label) in OtherrearningsNonTax" :key="label">
                <td>{{ label }}</td>
                <td class="right">{{ formatCurrency(amount) }}</td>
            </tr>

            <tr class="section-title">
                <td>Total Earnings</td>
                <td class="right">{{ formatCurrency(totalEarnings) }}</td>
            </tr>
            </table>

            <div class="section-title">Deductions</div>
            
            <table>
            <tr class="total">
                <td>Deduction</td>
                <td class="right">Amount</td>
            </tr>
            <tr v-for="(amount, label) in deductions" :key="label">
                <td>{{ label }}</td>
                <td class="right">{{ formatCurrency(amount) }}</td>
            </tr>

            <tr class="total" v-if="Object.keys(OtherdeductionsTax).length > 0">
                <td>Other Deduction Taxable</td>
                <td class="right">Amount</td>
            </tr>

            <tr v-for="(amount, label) in OtherdeductionsTax" :key="label">
                <td>{{ label }}</td>
                <td class="right">{{ formatCurrency(amount) }}</td>
            </tr>

            <tr class="total" v-if="Object.keys(OtherdeductionsNonTax).length > 0">
                <td>Other Deduction Non Taxable</td>
                <td class="right">Amount</td>
            </tr>

            <tr v-for="(amount, label) in OtherdeductionsNonTax" :key="label">
                <td>{{ label }}</td>
                <td class="right">{{ formatCurrency(amount) }}</td>
            </tr>

            

            <tr class="section-title">
                <td>Total Deductions</td>
                <td class="right">{{ formatCurrency(totalDeductions) }}</td>
            </tr>
            </table>

            <div class="section-title">Net Pay</div>
            <table>
            <tr>
                <td><strong>NET PAY:</strong></td>
                <td class="right"><strong>{{ formatCurrency(netPay) }}</strong></td>
            </tr>
            </table>

            <div class="footer">
            <!-- <p>Generated Payslip. For internal use only.</p> -->
            </div>
        </div>
    </a-drawer>
</template>
<script>
import {
    ArrowDownOutlined,
    ArrowUpOutlined,
    LoadingOutlined,
    MinusSquareOutlined,
    PlusOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import { computed, defineComponent, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import FormItemHeading from "../../../../common/components/common/typography/FormItemHeading.vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";
import hrmManagement from "../../../../common/composable/hrmManagement.js";
import StaffMemberAddButton from "../../staff-members/users/StaffAddButton.vue";

export default defineComponent({
    props: [
        "data",
        "formData",
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
        StaffMemberAddButton,
        ArrowUpOutlined,
        ArrowDownOutlined,
        MinusSquareOutlined,
        FormItemHeading,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { getMonthNameByNumber, formatMinutes } = hrmManagement();

        const { appSetting, permsArray, formatAmountCurrency } = common();
        const activeKey = ref("summary");
        const { t } = useI18n();
        const formFields = ref([]);
        const removedPriceGiven = ref([]);

        onMounted(() => {});

        const onSubmit = () => {
            addEditRequestAdmin({
                url: props.url,
                data: props.formData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        const summaryColumns = [
            {
                title: t("common.title"),
                dataIndex: "title",
            },
            {
                title: t("common.value"),
                dataIndex: "value",
            },
        ];
        const summaryData = ref([]);
        const leaveHoliday = ref([]);

        const salaryComponentsColumns = [
            {
                title: t("payroll.salary_component"),
                dataIndex: "title",
            },
            {
                title: t("common.value"),
                dataIndex: "value",
            },
        ];
        const salaryComponentsData = ref([]);

        const addFormField = () => {
            formFields.value.push({
                bouns: "",
                amount: "",
                is_half_day: 0,
            });
        };

        const removeFormField = (item) => {
            let index = formFields.value.indexOf(item);
            if (index !== -1) {
                formFields.value.splice(index, 1);
            }

            if (item.id != "") {
                removedPriceGiven.value.push(item.id);
            }
        };

        // watch(
        //     () => props.visible,
        //     (newVal, oldVal) => {
        //         console.log(props.data)
        //         summaryData.value = [
        //             {
        //                 key: "total_days",
        //                 title: t("payroll.total_days"),
        //                 value: props.data.total_days,
        //             },
        //             {
        //                 key: "working_days",
        //                 title: t("payroll.working_days"),
        //                 value: props.data.working_days,
        //             },
        //             {
        //                 key: "present_days",
        //                 title: t("payroll.present_days"),
        //                 value: props.data.present_days,
        //             },
        //             {
        //                 key: "total_office_time",
        //                 title: t("payroll.total_office_time"),
        //                 value: formatMinutes(props.data.total_office_time),
        //             },
        //             {
        //                 key: "total_worked_time",
        //                 title: t("payroll.total_worked_time"),
        //                 value: formatMinutes(props.data.total_worked_time),
        //             },
        //             {
        //                 key: "half_days",
        //                 title: t("payroll.half_days"),
        //                 value: props.data.half_days,
        //             },
        //             {
        //                 key: "late_days",
        //                 title: t("payroll.late_days"),
        //                 value: props.data.late_days,
        //             },
        //             {
        //                 key: "basic_salary",
        //                 title: t("payroll.basic_salary"),
        //                 value: formatAmountCurrency(props.data.basic_salary),
        //             },
        //             {
        //                 key: "sss_amount_ee_share",
        //                 title: t("payroll.sss_share"),
        //                 value: formatAmountCurrency(props.data.sss_share_ee),
        //             },
        //             {
        //                 key: "sss_amount_ee_mpf_share",
        //                 title: t("payroll.sss_share_ee_mpf"),
        //                 value: formatAmountCurrency(props.data.sss_mpf_ee),
        //             },
        //             {
        //                 key: "pagibig_share_ee",
        //                 title: t("payroll.pagibig_share_ee"),
        //                 value: formatAmountCurrency(props.data.pagibig_share_ee),
        //             },
        //             {
        //                 key: "philhealth_share_ee",
        //                 title: t("payroll.philhealth_share_ee"),
        //                 value: formatAmountCurrency(props.data.philhealth_share_ee),
        //             },
        //             {
        //                 key: "tax_withheld",
        //                 title: t("payroll.tax_withheld"),
        //                 value: formatAmountCurrency(props.data.tax_withheld),
        //             },
                    
                    
                    

        //         ];
        //         leaveHoliday.value = [
        //             {
        //                 key: "paid_leaves",
        //                 title: t("payroll.paid_leaves"),
        //                 value: props.data.paid_leaves,
        //             },
        //             {
        //                 key: "unpaid_leaves",
        //                 title: t("payroll.unpaid_leaves"),
        //                 value: props.data.unpaid_leaves,
        //             },
        //             {
        //                 key: "holiday_count",
        //                 title: t("payroll.holiday_count"),
        //                 value: props.data.holiday_count,
        //             },
        //         ];
        //         salaryComponentsData.value = [
        //             {
        //                 key: "salary_amount",
        //                 title: t("payroll.salary_amount"),
        //                 value: props.data.salary_amount,
        //             },
        //             {
        //                 key: "pre_payment_amount",
        //                 title: t("payroll.pre_payment_deduction"),
        //                 value: props.data.pre_payment_amount,
        //             },
        //             {
        //                 key: "expense_amount",
        //                 title: t("payroll.expense_claim"),
        //                 value: props.data.expense_amount,
        //             },
        //         ];
        //     }
        // );
        
        const OtherdeductionsTax = computed(() => {
            if (!props.data || !Array.isArray(props.data.payroll_detl)) return {};

            const result = {};
            props.data.payroll_detl
                .filter(item => item.types === 'DEDC' && item.isTaxable === true)
                .forEach(item => {
                    result[item.title] = item.amount || 0;
                });

            return result;
        });

        const OtherdeductionsNonTax = computed(() => {
            if (!props.data || !Array.isArray(props.data.payroll_detl)) return {};

            const result = {};
            props.data.payroll_detl
                .filter(item => item.types === 'DEDC' && item.isTaxable === false)
                .forEach(item => {
                    result[item.title] = item.amount || 0;
                });

            return result;
        });


        const OtherearningsTax = computed(() => {
            if (!props.data || !Array.isArray(props.data.payroll_detl)) return {};

            const result = {};
            props.data.payroll_detl
                .filter(item => item.types === 'EARN' && item.isTaxable === true)
                .forEach(item => {
                    result[item.title] = item.amount || 0;
                });

            return result;
        });

        const OtherrearningsNonTax = computed(() => {
            if (!props.data || !Array.isArray(props.data.payroll_detl)) return {};

            const result = {};
            props.data.payroll_detl
                .filter(item => item.types === 'EARN' && item.isTaxable === false)
                .forEach(item => {
                    result[item.title] = item.amount || 0;
                });

            return result;
        });

        
        const otherearnings = computed(() => {
            if (!props.data) return {};
            return {
                'Regular OT': props.data.regular_ot_amount || 0,
                'Legal Holiday': props.data.legal_holiday_amount || 0,
                'Legal Holiday OT': props.data.legal_holiday_ot_amount || 0,
                'Rest Day': props.data.rest_day_amount || 0,
                'Rest Day OT': props.data.rest_day_ot_amount || 0,
                'Night Differential': props.data.night_differential_amount || 0,
            };
        });

        const deductions = computed(() => {
            if (!props.data) return {};
            return {
                'SSS Contribution': props.data.sss_share_ee || 0,
                'MPF Contribution': props.data.sss_mpf_ee || 0,
                'Pag-ibig Contribution': props.data.pagibig_share_ee || 0,
                'Philhealth Contribution': props.data.philhealth_share_ee || 0,
                'Withholding Tax': props.data.tax_withheld || 0,
            };
        });
// console.log(otherearnings)
        const totalOT = computed(() =>
        
            Object.values(otherearnings.value).reduce((a, b) => a + b, 0)
        )

        const TotalOtherrearningsNonTax = computed(() =>
            Object.values(OtherrearningsNonTax.value).reduce((a, b) => a + b, 0)
        )
        
        const TotalOtherearningsTax = computed(() =>
            Object.values(OtherearningsTax.value).reduce((a, b) => a + b, 0)
        )


        const TotalOtherdeductionsNonTax = computed(() =>
            Object.values(OtherdeductionsNonTax.value).reduce((a, b) => a + b, 0)
        )
        
        const TotalOtherdeductionsTax = computed(() =>
            Object.values(OtherdeductionsTax.value).reduce((a, b) => a + b, 0)
        )

        const totalContriDeductions = computed(() =>
            Object.values(deductions.value).reduce((a, b) => a + b, 0)
        )
        

        const totalDeductions = computed(() =>
            (totalContriDeductions.value + TotalOtherdeductionsNonTax.value + TotalOtherdeductionsTax.value)
        )

        const earnings = computed(() => {
            if (!props.data) return {};
            return {
                basic: props.data.basic_salary || 0,
            };
        });

         const totalEarnings = computed(() =>
            earnings.value.basic + totalOT.value + TotalOtherrearningsNonTax.value + TotalOtherearningsTax.value
        )

        const netPay = computed(() =>
            props.data.net_salary
        )

        function formatCurrency(amount) {
        return amount.toLocaleString('en-PH', {
            style: 'currency',
            currency: 'PHP'
        })
        }
        return {
            netPay,
            OtherdeductionsTax,
            OtherdeductionsNonTax,
            OtherearningsTax,
            OtherrearningsNonTax,
            otherearnings,
            totalDeductions,
            totalOT,
            formatCurrency,
            totalEarnings,
            deductions,
            earnings,
            loading,
            rules,
            onClose,
            onSubmit,
            appSetting,
            permsArray,
            activeKey,
            formFields,
            removeFormField,
            addFormField,

            getMonthNameByNumber,
            formatAmountCurrency,

            summaryColumns,
            summaryData,
            leaveHoliday,
            salaryComponentsColumns,
            salaryComponentsData,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "70%",
        };
    },
});
</script>
<style scoped>
body {
  background: #f5f5f5;
  color: #333;
}
.payslip {
  background: #fff;
  padding: 30px;
  max-width: 100%;
  margin: 40px auto;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  font-family: Arial, sans-serif;
}
.header, .footer {
  text-align: center;
  margin-bottom: 20px;
}
.section-title {
  margin-top: 30px;
  font-weight: bold;
  border-bottom: 1px solid #ccc;
  padding-bottom: 5px;
}
table {
  width: 100%;
  margin-top: 10px;
  border-collapse: collapse;
}
th, td {
  padding: 8px 12px;
  border: 1px solid #ddd;
}
.total {
  font-weight: bold;
  background-color: #f0f0f0;
}
.right {
  text-align: right;
}
</style>