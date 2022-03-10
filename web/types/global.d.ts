interface Window {
    existLoading: boolean
    lazy: NodeJS.Timer
}

interface FormItemProps {
    name: string
    title: string
    value: any
    type: string
    content: any
    tip: string
    rule: any
}

interface anyObj {
    [key: string]: any
}
