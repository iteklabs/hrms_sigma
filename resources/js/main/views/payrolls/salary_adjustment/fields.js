import common from "@/common/composable/common";
import { useI18n } from "vue-i18n";


const fields = () => {
    const { user } = common();
    const { t } = useI18n();
    const url =
        "salary_adjustment?fields=xid,x_user_id,name,process_payment,cut_off,month,year,date_from,date_to,amount,type";
    const addEditUrl = "salary_adjustment";
    const hashableColumns = [
        'user_id'
    ];

    const initData = {
        name: "",
        process_payment: "date_range",
        cut_off: "A",
        month: "",
        year: "",
        date_from: "",
        date_to: "",
        amount: 0,
        type: "T",
        user_id: undefined,
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
            {
                title: t("salary_adjustment.date_from"),
                dataIndex: "date_from",
            },
            {
                title: t("salary_adjustment.date_to"),
                dataIndex: "date_to",
            },
            {
                title: t("salary_adjustment.amount"),
                dataIndex: "amount",
            },
            {
                title: t("salary_adjustment.type_taxable"),
                dataIndex: "type",
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
        {
            key: "date_from",
            value: t("salary_adjustment.date_from"),
        },
        {
            key: "date_to",
            value: t("salary_adjustment.date_to"),
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