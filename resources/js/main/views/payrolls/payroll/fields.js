import { ref } from "vue";
import { useI18n } from "vue-i18n";

const fields = () => {
    const url =
        "payrolls?fields=id,xid,user_id,night_differential_amount,legal_holiday_ot_amount,legal_holiday_amount,rest_day_ot_amount,rest_day_amount,regular_ot_amount,PayrollDetl{id,xid,payroll_id,salary_adjustment_id,title,amount,types,isTaxable},payrollComponents{id,xid,flags,payroll_id,x_payroll_id,type,value_type,amount,name,salary_component_id,x_salary_component_id,user_id,x_user_id,pre_payment_id,x_pre_payment_id,expense_id,x_expense_id},x_user_id,user{id,xid,name,profile_image,net_salary,profile_image_url,salary_group_id,x_salary_group_id,ctc_value,annual_ctc,calculation_type,basic_salary},month,year,payment_date,status,net_salary,total_days,working_days,present_days,total_office_time,total_worked_time,half_days,late_days,paid_leaves,unpaid_leaves,holiday_count,pre_payment_amount,expense_amount,basic_salary,sss_share_ee,sss_share_er,sss_mpf_ee,sss_mpf_er,sss_ec_er,pagibig_share_ee,pagibig_share_er,philhealth_share_ee,philhealth_share_er,taxable_income,tax_withheld,cut_off,salary_amount,user:designation{id,xid,name},user:location{id,xid,name},user:salaryGroup:salaryGroupComponents:salaryComponent{id,xid,name,value_type,type,monthly,semi_monthly,weekly,bi_weekly},user:basicSalaryDetails{id,xid,user_id,x_user_id,type,value_type,monthly,salary_component_id,x_salary_component_id}";
    const addEditUrl = "payrolls";
    const { t } = useI18n();
    const hashableColumns = ["user_id"];

    const initData = {
        user_id: undefined,
        month: undefined,
        year: undefined,
        payment_date: undefined,
        status: "generated",
        net_salary: "",
        cut_off: undefined
    };

    const columns = ref([
        {
            title: t("payroll.user_id"),
            dataIndex: "user_id",
        },
        {
            title: t("payroll.net_salary"),
            dataIndex: "net_salary",
        },
        {
            title: t("payroll.month"),
            dataIndex: "month",
        },
        {
            title: t("payroll.payment_date"),
            dataIndex: "payment_date",
        },
        {
            title: t("payroll.status"),
            dataIndex: "status",
        },
    ]);

    const filterableColumns = [
        {
            key: "name",
            value: t("common.name"),
        },
    ];

    return {
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        url,
    };
};

export default fields;
