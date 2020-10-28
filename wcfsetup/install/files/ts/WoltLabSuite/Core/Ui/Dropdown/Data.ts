export type NotificationAction = 'close' | 'open';
export type NotificationCallback = (containerId: string, action: NotificationAction) => void;
