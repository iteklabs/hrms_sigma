import common from "@/common/composable/common";
import { useI18n } from "vue-i18n";

const fields = (props) => {
    const { user, dayjs } = common();
    const { t } = useI18n();
    const url =
        "salary_deduction_loan?fields=xid,x_user_id,x_location_id,loan_id,type_of_loan,name,total_amount_loan,no_deductions,type_of_deduction,sched_of_deduction,monthly_deductions,amount_per_payroll,start_year_specific,start_month_specific,start_batch_specific,loan_name";
    const addEditUrl = "salary_deduction_loan";
    const hashableColumns = [
        'user_id',
        'location_id'
    ];
    const initData = {
        name: "",
        location_id: undefined,
        loan_type: '',
        user_id: undefined,
        total_amount_loan: 0,
        type_of_deduction: "monthly",
        sched_of_deduction: "A",
        payroll_deductions: 0,
        start_year_specific: null,
        start_month_specific: null,
        start_batch_specific: "A",
        loan_name: "",
        DataNeed: "",
        type_of_loan: "",
        no_deductions: 0,
        amount_per_payroll: 0,
        payroll_deduction: 0,
    };

    const columns = [
            { title: "User", dataIndex: "user_id" },
            { title: "Voucher/SOA", dataIndex: "loan_id" },
            { title: "Type of Loan", dataIndex: "type_of_loan" },
            { title: "Amount", dataIndex: "total_amount_loan" },
            { title: "Action", dataIndex: "action" },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("salary_adjustment.name"),
        },
    ];

    return {
        url,
        initData,
        columns,
        filterableColumns,
        addEditUrl,
        hashableColumns,
    };
};
export default fields;