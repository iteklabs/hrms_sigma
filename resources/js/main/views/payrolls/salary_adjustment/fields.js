import common from "@/common/composable/common";
import { useI18n } from "vue-i18n";


const fields = (props) => {
    const { user, dayjs } = common();
    const { t } = useI18n();
    const url =
        "salary_adjustment?fields=xid,x_user_id,name,process_payment,start_cut_off_specific,start_month_specific,start_year_specific,end_cut_off_specific,end_month_specific,end_year_specific,month_specific,cut_off_specific,year_specific,amount,type,adjustment_type";
    const addEditUrl = "salary_adjustment";
    const hashableColumns = [
        'user_id'
    ];
    console.log(props);
    const initData = {
        name: "",
        process_payment: "date_range",
        amount: 0,
        type: "T",
        adjustment_type: '',
        user_id: undefined,
        start_cut_off_specific: "A",
        start_month_specific: null,
        start_year_specific: null,
        end_cut_off_specific: null,
        end_month_specific: null,
        end_year_specific: null,
        cut_off_specific: null,
        month_specific: null,
        year_specific: null,
        // x_user_id: "",
        // user: ""
    };

     const columns = [
            {
                title: t("salary_adjustment.user"),
                dataIndex:"user_id",
            },
            {
                title: t("salary_adjustment.name"),
                dataIndex: "name",
            },
            // {
            //     title: t("salary_adjustment.date_from"),
            //     dataIndex: "date_from",
            // },
            // {
            //     title: t("salary_adjustment.date_to"),
            //     dataIndex: "date_to",
            // },
            {
                title: t("salary_adjustment.amount"),
                dataIndex: "amount",
            },
            {
                title: t("salary_adjustment.type_taxable"),
                dataIndex: "type",
            },
            {
                title: t("salary_adjustment.adjustment_type"),
                dataIndex: "adjustment_type",
            },
            {
                title: t("common.action"),
                dataIndex: "action",
            },
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