import { useI18n } from "vue-i18n";

const fields = () => {
    const addEditUrl = "holidays";
    const { t } = useI18n();
    const hashableColumns = [];

    const initData = {
        name: "",
        year: "",
        month: "",
        holiday_type: "RH",
        date: undefined,
    };

    const columns = [
        {
            title: t("holiday.name"),
            dataIndex: "name",
        },
        {
            title: t("holiday.date"),
            dataIndex: "date",
        },
        {
            title: t("holiday.holiday_type"),
            dataIndex: "string",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

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
    };
};

export default fields;
